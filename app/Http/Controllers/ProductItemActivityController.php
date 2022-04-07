<?php

namespace App\Http\Controllers;

use App\Events\ProductItemUpsert;
use App\Http\Requests\StoreProductItemActivityRequest;
use App\Models\Customer;
use App\Models\EntryRemark;
use App\Models\ProductItem;
use App\Models\ProductItemActivity;
use App\Models\ProductItemWarrant;
use App\Models\Warehouse;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
                'eventable', 'productItem', 'contract']);

        return $productItem->activities()
            ->with($meta->include)
            ->paginate($meta->limit, '*', 'page', $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param ProductItem $productItem
     * @return array|\Illuminate\Http\Response
     */
    public function store(StoreProductItemActivityRequest $request, ProductItem $productItem)
    {
        DB::beginTransaction();

        $productItem->replicate();

        $latestActivity = $productItem->latestActivity;

        $activity = $latestActivity->replicate(['id']);

        $remark = new EntryRemark;
        $remark->description = $request->get('description');
        $remark->save();

        $activity->remark()->associate($remark);

        $activity->log_category_code = $request->get('category_code');
        $activity->log_category_title = ProductItemActivityUtils::activityCategoryTitles()
        [$request->get('category_code')];

        if ($request->get('category_code') ==
            ProductItemActivityUtils::activityCategoryCodes()['WARRANTY_UPDATE']) {

            $warrant = new ProductItemWarrant;
            $warrant->warrant_start = $request->get('warrant_start');
            $warrant->warrant_end = $request->get('warrant_end');
            $warrant->productItem()->associate($productItem);
            $warrant->customer()->associate($productItem->latestActivity->location);
            $warrant->save();

            $activity->warrant()->associate($warrant);


            //clone contract only if is active
            if (isset($latestActivity->customer_contract_id)) {
                $contract = $latestActivity->contract;

                if (Carbon::parse($contract->start_date)->addDay()->isPast()
                    && Carbon::parse($contract->expiry_date)->addDay()->isFuture()) {
                    $activity->contract()->associate($contract);
                } else {
                    $activity->customer_contract_id = null;
                }
            }

        } elseif ($request->get('category_code') ==
            ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_WAREHOUSE_TRANSFER']) {

            $activity->location()
                ->associate(Warehouse::findOrFail($request->get('warehouse_id')));

            $activity->customer_contract_id = null; //reset contract

            if ($request->filled('out_of_order')) { //just in case 🤗
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
                    ProductItemUpsert::dispatch($productItem->product, -1);
                }
            }

        } elseif ($request->get('category_code') ==
            ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_CUSTOMER_TRANSFER']) {

            $activity->location()
                ->associate(Customer::findOrFail($request->get('customer_id')));
            $activity->customer_contract_id = null; //reset contract

        }

        $activity->save();
        DB::commit();
        $activity->load(['createdBy','location']);

        return ['data' => $activity];
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
