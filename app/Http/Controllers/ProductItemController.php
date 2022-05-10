<?php

namespace App\Http\Controllers;

use App\Events\ProductItemUpsert;
use App\Http\Requests\StoreProductItemRequest;
use App\Http\Requests\UpdateProductItemRequest;
use App\Models\Customer;
use App\Models\EntryRemark;
use App\Models\ProductItem;
use App\Models\ProductItemActivity;
use App\Models\ProductItemWarrant;
use App\Models\Warehouse;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use JetBrains\PhpStorm\ArrayShape;

class ProductItemController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(ProductItem::class, 'product_item',
            ['except' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        if ($request->search) {
            $this->authorize('search', ProductItem::class);
        } else {
            Gate::allowIf(function ($user) {
                return $user->role->permissions->contains('name', 'productItems.view') ||
                    $user->role->permissions->contains('name', 'worksheets.create');
            });
        }

        $meta = $this->queryMeta(['created_at', 'product_id', 'sn', 'serial_number'],
            ['createdBy', 'updatedBy', 'product', 'latestActivity', 'latestActivity.location',
                'latestActivity.warrant', 'activities', 'activities.location', 'activities.warrant',
                'activities.createdBy', 'activities.remark', 'activities.repair',
                'activeWarrant', 'oldestActivity']);


        return ProductItem::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {

                $query->where(function ($query) use ($searchTerm) {
                    $query->whereLike('sn', $searchTerm);
                    $query->orWhereLike('serial_number', $searchTerm);
                });
            })
            ->when($request->get('warehouse_id'), function ($query, $warehouseId) {
                //should only return items in a specified warehouse
                $query->whereRelation('latestActivity', 'location_id', $warehouseId);
                $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Warehouse::class));
                $query->whereRelation('latestActivity', 'location_type', $morphKey);
            })
            ->when($request->get('outOfOrder'), function ($query) {
                //should only include items with specified out of order status
                $query->where('out_of_order', \request()->boolean('outOfOrder'));
            })
            ->when($request->get('customer_id'), function ($query, $customer_id) {
                //should only include items in specified customer
                $query->whereRelation('latestActivity', 'location_id', $customer_id);
                $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Customer::class));
                $query->whereRelation('latestActivity', 'location_type', $morphKey);
            })
            ->when($request->get('product_id'), function ($query, $productId) {
                //only return of specific model
                $query->where('product_id', $productId);
            })
            ->when($request->get('excludedItems'), function ($query, $excluded) {
                $query->whereNotIn('id', explode(',', $excluded));
            })
            ->when($request->get('total'), function ($query) {
                $query->withCount('activities');
            })
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
     * @param StoreProductItemRequest $request
     * @return ProductItem[]
     */
    #[ArrayShape(['data' => "\App\Models\ProductItem"])]
    public function store(StoreProductItemRequest $request): array
    {

        DB::beginTransaction();

        $productItem = new ProductItem;
        $productItem->product_id = $request->get('product_id');
        $productItem->serial_number = $request->get('serial_number');
        $productItem->purchase_order_id = $request->get('purchase_order_id');
        $productItem->out_of_order = $request->boolean('out_of_order', false);
        $productItem->save();

        $categoryCode = $request->get('category_code');
        $categoryTitle = ProductItemActivityUtils::activityCategoryTitles()[$categoryCode];

        $productItemActivity = new ProductItemActivity;
        $productItemActivity->customer_contract_id = $request->get('contract_id');
        $productItemActivity->log_category_code = $categoryCode;
        $productItemActivity->log_category_title = $categoryTitle;
        $productItemActivity->productItem()->associate($productItem);

        if ($request->filled('warrant_start')) {
            $warrant = new ProductItemWarrant;
            $warrant->customer_id = $request->get('customer_id');
            $warrant->warrant_start = $request->get('warrant_start');
            $warrant->warrant_end = $request->get('warrant_end');
            $warrant->productItem()->associate($productItem);
            $warrant->save();
            $productItemActivity->warrant()->associate($warrant);
        }

        if ($request->filled('warehouse_id')) {
            $productItemActivity->location()
                ->associate(Warehouse::find($request->get('warehouse_id')));
        } else {
            $productItemActivity->covenant = $request->get('nature_of_release');

            $productItemActivity->location()
                ->associate(Customer::find($request->get('customer_id')));

        }

        if ($request->filled('description')) {
            $remark = new EntryRemark;
            $remark->description = $request->filled('description');
            $productItemActivity->remark()->associate($remark);
        }

        $productItemActivity->save();

        /**
         * Increment Quantity In by one if:
         * -item is in good shape
         * - is in warehouse
         * - has no PO
         */

        if (!$request->boolean('out_of_order', false) &&
            !$request->filled('purchase_order_id') && $request->filled('warehouse_id')) {
            ProductItemUpsert::dispatch($productItem->product, 1);
        }

        DB::commit();

        $productItem->load(['product', 'latestActivity.location', 'latestActivity.warrant']);
        return ['data' => $productItem];
    }

    /**
     * Display the specified resource.
     *
     * @param ProductItem $productItem
     * @return ProductItem[]
     */
    #[ArrayShape(['data' => "\App\Models\ProductItem"])]
    public function show(ProductItem $productItem): array
    {
        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'product', 'latestActivity',
            'latestActivity.location', 'latestActivity.warrant', 'activities',
            'activities.location', 'activities.warrant', 'activities.createdBy',
            'activities.remark', 'activities.repair', 'activeWarrant',
            'oldestActivity']);

        $productItem->load($meta->include);

        return ['data' => $productItem];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductItemRequest $request
     * @param ProductItem $productItem
     * @return ProductItem[]
     */
    #[ArrayShape(['data' => "\App\Models\ProductItem"])]
    public function update(UpdateProductItemRequest $request, ProductItem $productItem)
    {

        DB::beginTransaction();


        $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Warehouse::class));

        //If the item is currently in warehouse:
        if ($productItem->latestActivity->location_type == $morphKey) {

            // If the item didn't have PO and was not out_of order
            if (!isset($productItem->purchase_order_id) && $productItem->out_of_order) {
                // if  now we have PO or  is out of order, decrement Qty In
                if ($request->boolean('out_of_order', false) ||
                    $request->filled('purchase_order_id')) {
                    ProductItemUpsert::dispatch($productItem->product, -1);
                }

            } //if item had PO or was out of order
            elseif (!isset($productItem->purchase_order_id) || $productItem->out_of_order == true) {
                //if the item now is in order and has no purchase order, increment Qty In
                if (!$request->boolean('out_of_order', false) &&
                    !$request->filled('purchase_order_id')) {
                    ProductItemUpsert::dispatch($productItem->product, 1);
                }
            }
        }

        $productItem->product_id = $request->get('product_id', $productItem->product_id);
        $productItem->serial_number = $request->get('serial_number', $productItem->serial_number);
        $productItem->purchase_order_id = $request->get('purchase_order_id', $productItem->purchase_order_id);
        $productItem->out_of_order = $request->boolean('out_of_order', $productItem->out_of_order);

        $productItem->update();
        $productItem->refresh();

        DB::commit();
        $productItem->load(['product', 'latestActivity.location', 'latestActivity.warrant']);
        return ['data' => $productItem];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ProductItem $productItem
     * @return Response
     */
    public function destroy(ProductItem $productItem): Response
    {
        $productItem->delete();
        return response()->noContent();
    }
}
