<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RequestForQuotationItem;
use App\Models\UnitOfMeasure;
use App\Services\PurchaseOrderService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(PurchaseOrder::class, 'purchase_order');

    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $meta = $this->queryMeta(['created_at', 'sn'], ['createdBy', 'updatedBy', 'items', 'rfq']);

        return PurchaseOrder::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);

                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);
                });
            })
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, '*', $meta->page);
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

        if ($request->boolean('download', false)) {
            //TODO generate the docs, zip and download
        }

        return ['data' => $purchaseOrder];
    }

    /**
     * Display the specified resource.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return array
     */
    public function show(PurchaseOrder $purchaseOrder): array
    {
        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'items', 'rfq']);

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

        if ($request->boolean('download', false)) {
            //TODO generate the docs, zip and download
        }

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
}
