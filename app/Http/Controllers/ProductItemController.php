<?php

namespace App\Http\Controllers;

use App\Events\ProductItemUpsert;
use App\Http\Requests\StoreProductItemRequest;
use App\Http\Requests\UpdateProductItemRequest;
use App\Models\Customer;
use App\Models\EntryRemark;
use App\Models\ProductItem;
use App\Models\ProductTrackingLog;
use App\Models\ProductWarrant;
use App\Models\Warehouse;
use App\Utils\ProductTrackingUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class ProductItemController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(ProductItem::class, 'product_item');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'product_id', 'sn', 'serial_number'],
            ['createdBy', 'updatedBy', 'product', 'latestEntryLog', 'latestEntryLog.location',
                'latestEntryLog.warrant', 'entryLogs', 'entryLogs.location', 'entryLogs.warrant',
                'entryLogs.createdBy', 'entryLogs.remark', 'entryLogs.repair.sparesUtilized', 'entryLogs.repair.sparesUtilized.product']);

        return ProductItem::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->whereLike('sn', $searchTerm);
                $query->orWhereLike('serial_number', $searchTerm);
            })
            ->when($request->get('warehouse_id'), function ($query, $warehouseId) {
                //should only issue items in specified warehouse
                $query->whereRelation('latestEntryLog', 'location_id', $warehouseId);
                $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Warehouse::class));
                $query->whereRelation('latestEntryLog', 'location_type', $morphKey);
            })
            ->when($request->get('outOfOrder'), function ($query) {
                //should only issue items in warehouse
                $query->where('out_of_order', \request()->boolean('outOfOrder'));
            })
            ->when($request->get('customer_id'), function ($query, $customer_id) {
                //should only issue items in specified warehouse
                $query->whereRelation('latestEntryLog', 'location_id', $customer_id);
                $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Customer::class));
                $query->whereRelation('latestEntryLog', 'location_type', $morphKey);
            })
            ->when($request->get('total'), function ($query) {
                $query->withCount('entryLogs');
            })
            ->paginate($meta->limit, '*', 'page', $meta->page);
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
        $productItem->out_of_order = $request->get('out_of_order') ?? false;
        $productItem->save();

        $categoryCode = $request->get('category_code');
        $categoryTitle = ProductTrackingUtils::getLogCategories()[$categoryCode];

        $trackingEntry = new ProductTrackingLog;
        $trackingEntry->customer_contract_id = $request->get('contract_id');
        $trackingEntry->log_category_code = $categoryCode;
        $trackingEntry->log_category_title = $categoryTitle;
        $trackingEntry->productItem()->associate($productItem);

        if ($request->filled('warrant_start')) {
            $warrant = new ProductWarrant;
            $warrant->customer_id = $request->get('customer_id');
            $warrant->warrant_start = $request->get('warrant_start');
            $warrant->warrant_end = $request->get('warrant_end');
            $warrant->productItem()->associate($productItem);
            $warrant->save();
            $trackingEntry->warrant()->associate($warrant);
        }

        if ($request->filled('warehouse_id')) {
            $trackingEntry->location()->associate(Warehouse::find($request->get('warehouse_id')));
        } else {
            $trackingEntry->location()->associate(Customer::find($request->get('customer_id')));

        }

        if ($request->filled('description')) {
            $remark = new EntryRemark;
            $remark->description = $request->filled('description');
            $trackingEntry->remark()->associate($remark);
        }

        $trackingEntry->save();
        DB::commit();

        //if increment by is not zero,fire product item upsert event
        if (!empty($request->get('increment_stock_by'))
            && $request->get('increment_stock_by') != 0) {
            ProductItemUpsert::dispatch($productItem->product,
                $request->get('increment_stock_by'));
        }

        $productItem->load(['latestEntryLog.location', 'latestEntryLog.warrant', 'product']);

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
        $meta = $this->queryMeta([],
            ['createdBy', 'updatedBy', 'product', 'entryLogs', 'latestEntryLog',
                'latestEntryLog.location', 'latestEntryLog.warrant']);
        $productItem->load($meta->include);

        return ['data' => $productItem];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateProductItemRequest $request
     * @param ProductItem $productItem
     * @return ProductItem[]
     */
    #[ArrayShape(['data' => "\App\Models\ProductItem"])]
    public function update(UpdateProductItemRequest $request, ProductItem $productItem)
    {

        $productItem->product_id = $request->get('product_id') ?? $productItem->product_id;
        $productItem->serial_number = $request->get('serial_number') ??
            $productItem->serial_number;
        $productItem->purchase_order_id = $request->get('purchase_order_id') ??
            $productItem->purchase_order_id;

        $productItem->update();
        $productItem->refresh();

        //if increment by is not zero,fire product item upsert event
        if (!empty($request->get('increment_stock_by'))
            && $request->get('increment_stock_by') != 0) {
            ProductItemUpsert::dispatch($productItem->product,
                $request->get('increment_stock_by'));
        }
        $productItem->load(['latestEntryLog.location', 'latestEntryLog.warrant', 'product']);

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
