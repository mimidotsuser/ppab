<?php

namespace App\Http\Controllers;

use App\Actions\GeneratePODoc;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RequestForQuotationItem;
use App\Models\UnitOfMeasure;
use App\Services\PurchaseOrderService;
use App\Utils\PurchaseOrderUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(PurchaseOrder::class, 'purchase_order',
            ['except' => ['index']]);

    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {

        if ($request->search) {
            $this->authorize('search', PurchaseOrder::class);
        } else {
            $this->authorize('viewAny', PurchaseOrder::class);
        }

        $meta = $this->queryMeta(['created_at', 'sn'], ['createdBy', 'updatedBy', 'rfq', 'items']);


        return PurchaseOrder::with($meta->include)
            ->when($request->search, function (Builder $query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);

                    $query->orWhereRelationBeginsWith('vendor', 'name', $searchTerm);
                    $query->orWhereRelationLike('vendor', 'name', $searchTerm);
                });
            })
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->when($request->boolean('withDeliveredQty', false), function (Builder $builder) {
                $builder->with(['items' => function ($query) {
                    $query->leftJoinSub(PurchaseOrderUtils::deliveredItemsSubQuery(),
                        'deliveredItems', 'purchase_order_items.id', '=', 'deliveredItems.po_item_id');
                }]);
            })
            ->when($request->boolean('undeliveredQtyOnly', false), function (Builder $builder) {
                $builder->whereExists(function (QueryBuilder $builder) {
                    $builder->from(PurchaseOrderItem::query()->from)
                        ->leftJoinSub(PurchaseOrderUtils::deliveredItemsSubQuery(),
                            'deliveredItems', 'purchase_order_items.id', '=', 'deliveredItems.po_item_id')
                        ->joinSub(PurchaseOrderUtils::purchaseOrderTotalQtySubQuery(),
                            'total_ordered', 'purchase_order_items.id', '=', 'total_ordered.id')
                        ->whereRaw('(`total_qty`- IFNULL(`delivered_qty`,0))>0');
                });
            })
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePurchaseOrderRequest $request
     * @param PurchaseOrderService $service
     * @return array
     */
    public function store(StorePurchaseOrderRequest $request, PurchaseOrderService $service): array
    {
        DB::beginTransaction();

        $purchaseOrder = new PurchaseOrder;
        $purchaseOrder->rfq_id = $request->get('rfq_id');
        $purchaseOrder->doc_validity = $request->get('doc_validity');
        $purchaseOrder->vendor_id = $request->get('vendor_id');
        $purchaseOrder->currency_id = $request->get('currency_id');
        $purchaseOrder->save();
        $purchaseOrder->refresh();

        $items = [];
        $productsItemWithBalDiff = [];
        foreach ($request->get('items') as $row) {
            $item = new PurchaseOrderItem;
            $item->product_id = $row['product_id'];
            $item->qty = $row['qty'];
            $item->unit_price = $row['unit_price'];
            $item->unit_of_measure_id = $row['unit_of_measure_id'];
            $item->request()->associate($purchaseOrder);

            $items[] = $item;

            $uom = UnitOfMeasure::findOrFail($row['unit_of_measure_id']);
            if (isset($row['rfq_item_id'])) {
                $rfqItemModel = RequestForQuotationItem::findOrFail($row['rfq_item_id']);

                $diff = ($uom->unit * $row['qty']) - ($rfqItemModel->uom->unit * $rfqItemModel->qty);
                //if the quantity is not equal to rfq Item qty
                if ($diff != 0) {
                    $productsItemWithBalDiff[] = [
                        'id' => $row['product_id'],
                        'by' => $diff
                    ];
                }
                $item->rfqItem()->associate($rfqItemModel);
            } else {
                //increment the  balance
                $productsItemWithBalDiff[] = [
                    'id' => $row['product_id'],
                    'by' => $uom->unit * $row['qty']
                ];
            }
        }

        $purchaseOrder->items()->saveMany($items);

        $service->updateProductB2BBalance($productsItemWithBalDiff);

        DB::commit();

        return ['data' => $purchaseOrder];
    }

    /**
     * Display the specified resource.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return array|Response
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'items', 'rfq']);

        if (\request()->boolean('undeliveredQtyOnly', false)) {
            $query = $purchaseOrder->items()->whereExists(function (QueryBuilder $builder) {
                $builder->from(PurchaseOrderItem::query()->from)
                    ->joinSub(PurchaseOrderUtils::deliveredItemsSubQuery(),
                        'deliveredItems', 'purchase_order_items.id', '=', 'deliveredItems.po_item_id')
                    ->joinSub(PurchaseOrderUtils::purchaseOrderTotalQtySubQuery(),
                        'total_ordered', 'purchase_order_items.id', '=', 'total_ordered.id')
                    ->whereRaw('`total_qty`-`delivered_qty`>0');
            });
            if ($query->doesntExist()) {
                return \response()->noContent(404);
            }
        }

        if (\request()->boolean('withDeliveredQty', false)) {
            $purchaseOrder->load(['items' => function ($query) {
                $query->joinSub(PurchaseOrderUtils::deliveredItemsSubQuery(),
                    'deliveredItems', 'purchase_order_items.id', '=', 'deliveredItems.po_item_id');
            }]);
        }

        $purchaseOrder->load($meta->include);

        return ['data' => $purchaseOrder];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseOrderRequest $request
     * @param PurchaseOrder $purchaseOrder
     * @param PurchaseOrderService $service
     * @return PurchaseOrder[]
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder,
                           PurchaseOrderService       $service)
    {
        DB::beginTransaction();

        $purchaseOrder->rfq_id = $request->get('rfq_id');
        $purchaseOrder->doc_validity = $request->get('doc_validity');
        $purchaseOrder->vendor_id = $request->get('vendor_id');
        $purchaseOrder->currency_id = $request->get('currency_id');
        $purchaseOrder->update();
        $purchaseOrder->refresh();

        if ($request->has('items')) {
            $items = [];
            $productsItemWithBalDiff = [];


            //for current existing items,
            foreach ($purchaseOrder->items as $item) {
                //if it has no rfq item id, mark it for stock balance deduction
                if (!isset($item->rfq_item_id)) {
                    $productsItemWithBalDiff[] = [
                        'id' => $item['product_id'],
                        'by' => $item['qty'] * -1 * $item->uom->unit
                    ];
                } else {
                    $model = $item->rfqItem;
                    $diff = ($model->qty * $model->uom->unit) - ($item['qty'] * $item->uom->unit);
                    if ($diff != 0) {
                        //reset to how it was before PO
                        $productsItemWithBalDiff[] = [
                            'id' => $item['product_id'],
                            'by' => $diff
                        ];
                    }
                }
            }

            //delete existing items
            $purchaseOrder->items()->delete();


            foreach ($request->get('items') as $row) {
                $item = new PurchaseOrderItem;
                $item->product_id = $row['product_id'];
                $item->qty = $row['qty'];
                $item->unit_price = $row['unit_price'];
                $item->unit_of_measure_id = $row['unit_of_measure_id'];
                $item->request()->associate($purchaseOrder);

                $items[] = $item;

                $uom = UnitOfMeasure::findOrFail($row['unit_of_measure_id']);
                if (isset($row['rfq_item_id'])) {
                    $rfqItemModel = RequestForQuotationItem::findOrFail($row['rfq_item_id']);

                    $diff = ($uom->unit * $row['qty']) - ($rfqItemModel->uom->unit * $rfqItemModel->qty);
                    //if the quantity is not equal to rfq Item qty
                    if ($diff != 0) {
                        $productsItemWithBalDiff[] = [
                            'id' => $row['product_id'],
                            'by' => $diff
                        ];
                    }
                    $item->rfqItem()->associate($rfqItemModel);
                } else {
                    //increment the  balance
                    $productsItemWithBalDiff[] = [
                        'id' => $row['product_id'],
                        'by' => $uom->unit * $row['qty']
                    ];
                }
            }

            $purchaseOrder->items()->saveMany($items);

            $service->updateProductB2BBalance($productsItemWithBalDiff);
        }
        DB::commit();

        return ['data' => $purchaseOrder];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param PurchaseOrderService $service
     * @return Response
     */
    public function destroy(PurchaseOrder $purchaseOrder, PurchaseOrderService $service)
    {
        DB::beginTransaction();

        $productsItemWithBalDiff = [];
        //reset restock quantity
        foreach ($purchaseOrder->items as $item) {
            //if it has no rfq item id, mark it for stock balance deduction
            if (!isset($item->rfq_item_id)) {
                $productsItemWithBalDiff[] = [
                    'id' => $item['product_id'],
                    'by' => $item['qty'] * -1 * $item->uom->unit
                ];
            } else {
                $model = $item->rfqItem;
                $diff = ($model->qty * $model->uom->unit) - ($item['qty'] * $item->uom->unit);
                if ($diff != 0) {
                    //reset to how it was before PO
                    $productsItemWithBalDiff[] = [
                        'id' => $item['product_id'],
                        'by' => $diff
                    ];
                }
            }
        }

        $service->updateProductB2BBalance($productsItemWithBalDiff);

        $purchaseOrder->delete();
        DB::commit();
        return \response()->noContent();
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param GeneratePODoc $PODoc
     * @return void
     * @throws AuthorizationException
     */
    public function downloadPurchaseOrderDocs(PurchaseOrder $purchaseOrder, GeneratePODoc $PODoc)
    {

        $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load(['items.uom', 'items.product', 'vendor']);

        $total = $purchaseOrder->items->reduce(function ($acc, $item) {
            return $acc += $item->qty * $item->uom->unit * $item->unit_price;
        }, 0);

        $PODoc($purchaseOrder, $total,)
            ->stream('po-' . strtolower($purchaseOrder->sn) . ".pdf");
    }
}
