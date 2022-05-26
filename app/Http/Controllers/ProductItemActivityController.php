<?php

namespace App\Http\Controllers;

use App\Contracts\ProductItemActivityContract;
use App\Events\ProductItemUpsert;
use App\Http\Requests\StoreProductItemActivityRequest;
use App\Models\Customer;
use App\Models\EntryRemark;
use App\Models\ProductItem;
use App\Models\ProductItemActivity;
use App\Models\ProductItemWarrant;
use App\Models\Warehouse;
use App\Services\ProductItemService;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductItemActivityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductItemActivity::class, 'product_item_activity');
    }

    /**
     * Display a listing of the resource.
     *
     * @param ProductItem $productItem
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(ProductItem $productItem)
    {
        $meta = $this->queryMeta(['created_at', 'product_id', 'sn', 'serial_number'],
            ['createdBy', 'updatedBy', 'product', 'location', 'warrant', 'remark', 'repair',
                'eventable', 'productItem', 'contract', 'repair.sparesUtilized']);

        return $productItem->activities()
            ->with($meta->include)
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param ProductItem $productItem
     * @return array|\Illuminate\Http\Response
     */
    public function store(StoreProductItemActivityRequest $request, ProductItem $productItem,
                          ProductItemService              $productItemService)
    {
        DB::beginTransaction();

        $latestActivity = $productItem->latestActivity;


        $remark = new EntryRemark;
        $remark->description = $request->get('description');
        $remark->save();

        $activity = new ProductItemActivityContract;
        $activity->remark = $remark;
        $activity->categoryCode = $request->get('category_code');
        $activity->categoryTitle = ProductItemActivityUtils::activityCategoryTitles()[$request->get('category_code')];
        $activity->covenant = $latestActivity->covenant;
        $activity->productItem = $productItem;

        if ($request->get('category_code') ==
            ProductItemActivityUtils::activityCategoryCodes()['WARRANTY_UPDATE']) {

            $warrant = new ProductItemWarrant;
            $warrant->warrant_start = $request->get('warrant_start');
            $warrant->warrant_end = $request->get('warrant_end');
            $warrant->productItem()->associate($productItem);
            $warrant->customer()->associate($productItem->latestActivity->location);
            $warrant->save();

            $activity->customer = $productItem->latestActivity->location;

        } elseif ($request->get('category_code') == ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_WAREHOUSE_TRANSFER']) {

            //update product item status if different
            if ($productItem->out_of_order != $request->boolean('out_of_order')) {
                $productItem->out_of_order = $request->boolean('out_of_order', false);
                $productItem->update();
            }

            $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Customer::class));
            //if item is not out of order and is from customer,
            if ($request->boolean('out_of_order') === false
                && $latestActivity->location_type == $morphKey) {
                // increment the product stock balance
                ProductItemUpsert::dispatch($productItem->product, 1);
            }

            $activity->warehouse = Warehouse::findOrFail($request->get('warehouse_id'));

        } elseif ($request->get('category_code') == ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_CUSTOMER_TRANSFER']) {

            $activity->customer = Customer::findOrFail($request->get('customer_id'));
            $activity->covenant = $request->get('purpose_code');
        }

        $model = $productItemService->serializeActivity($activity);

        $model->save();
        DB::commit();

        $model->refresh();
        $model->load(['createdBy', 'location']);

        return ['data' => $model];
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param ProductItem $productItem
     * @param ProductItemActivity $productItemActivity
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductItem $productItem, ProductItemActivity $productItemActivity)
    {

        $safeActivityCategories = Arr::except(ProductItemActivityUtils::activityCategoryCodes(),
            [
                ProductItemActivityUtils::activityCategoryCodes()['INITIAL_ENTRY'],
                ProductItemActivityUtils::activityCategoryCodes()['MATERIAL_REQUISITION_ISSUED'],
            ]
        );

        $isSafeActivity = Arr::exists($safeActivityCategories, $productItemActivity->log_category_code);

        DB::beginTransaction();
        //can only delete latest
        if ($productItem->latestActivity->id != $productItemActivity->id || !$isSafeActivity) {
            return response()->noContent(403);
        }

        $customerMorphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Customer::class));
        $warehouseMorphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Warehouse::class));

        /**
         *
         */
        $lastActivities = $productItem->activities->take(2);
        if (count($lastActivities) > 1) {
            //if location before the one to be deleted was customer and machine is in order,

            if ($productItem->out_of_order === false
                && $lastActivities->first()->location_type == $customerMorphKey
                && $productItemActivity->location_type == $warehouseMorphKey) {
                // decrement the product stock balance
                ProductItemUpsert::dispatch($productItem->product, -1);
            }
        } elseif ($productItem->out_of_order === false
            && $lastActivities->first()->location_type == $warehouseMorphKey) {
            //if we have no other record somehow, decrement the product stock balance
            ProductItemUpsert::dispatch($productItem->product, -1);
        }

        $productItemActivity->delete();
        DB::commit();
        return response()->noContent();
    }
}
