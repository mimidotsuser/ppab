<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestForQuotationRequest;
use App\Http\Requests\UpdateRequestForQuotationRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RequestForQuotation;
use App\Models\RequestForQuotationItem;
use App\Services\RequestForQuotationService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestForQuotationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(RequestForQuotation::class, 'request_for_quotation');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'sn'], ['createdBy', 'updatedBy',
            'items', 'vendors', 'purchaseRequest']);

        return RequestForQuotation::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
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
     * @param StoreRequestForQuotationRequest $request
     * @return RequestForQuotation[]
     */
    public function store(StoreRequestForQuotationRequest $request, RequestForQuotationService $service): array
    {
        DB::beginTransaction();

        $rfq = new RequestForQuotation;
        $rfq->purchase_request_id = $request->get('purchase_request_id');
        $rfq->closing_date = $request->get('closing_date');
        $rfq->save();
        $rfq->refresh();

        if ($request->has('vendors')) {
            $vendorIds = array_map(fn($r) => $r['id'], $request->get('vendors'));
            $rfq->vendors()->attach($vendorIds,
                ['created_by_id' => Auth::id(), 'updated_by_id' => Auth::id(),
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
                ]);
        }

        if ($request->has('items')) {
            $items = [];
            $productsItemWithBalDiff = [];
            foreach ($request->get('items') as $row) {
                $item = new RequestForQuotationItem;
                $item->product_id = $row['product_id'];
                $item->qty = $row['qty'];
                $item->unit_of_measure_id = $row['unit_of_measure_id'];

                if (isset($row['purchase_request_item_id'])) {
                    $prItemModel = PurchaseRequestItem::findOrFail($row['purchase_request_item_id']);
                    //check the diff
                    if ($prItemModel->approved_qty != $row['qty']) {
                        $productsItemWithBalDiff[] = [
                            'id' => $row['product_id'],
                            'by' => $row['qty'] - $prItemModel->approved_qty
                        ];
                    }
                    $item->purchaseRequestItem()->associate($prItemModel);
                } else {
                    $productsItemWithBalDiff[] = ['id' => $row['product_id'], 'by' => $row['qty']];
                }

                $items[] = $item;
            }

            $rfq->items()->saveMany($items);

            $service->updateProductB2BBalance($productsItemWithBalDiff);
        }

        DB::commit();

        if ($request->boolean('download', false)) {
            //TODO generate the doc
        }

        return ['data' => $rfq];
    }

    /**
     * Display the specified resource.
     *
     * @param RequestForQuotation $requestForQuotation
     * @return RequestForQuotation[]
     */
    public function show(RequestForQuotation $requestForQuotation)
    {
        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'items', 'vendors',
            'purchaseRequest']);

        $requestForQuotation->load($meta->include);
        return ['data' => $requestForQuotation];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequestForQuotationRequest $request
     * @param RequestForQuotation $requestForQuotation
     * @return RequestForQuotation[]
     */
    public function update(UpdateRequestForQuotationRequest $request,
                           RequestForQuotation              $requestForQuotation,
                           RequestForQuotationService       $service)
    {
        DB::beginTransaction();

        $requestForQuotation->purchase_request_id = $request->get('purchase_request_id');
        $requestForQuotation->closing_date = $request->get('closing_date');
        $requestForQuotation->update();
        $requestForQuotation->refresh();

        if ($request->has('vendors')) {

            $requestForQuotation->vendors()->detach(); //drop all from immediate/pivot table

            $vendorIds = array_map(fn($r) => $r['id'], $request->get('vendors'));
            $requestForQuotation->vendors()->attach($vendorIds,
                ['created_by_id' => Auth::id(), 'updated_by_id' => Auth::id(),
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
                ]);
        }

        if ($request->has('items')) {
            $items = [];
            $productsItemWithBalDiff = [];

            //for current existing items
            foreach ($requestForQuotation->items as $item) {
                //if it has no purchase request item id, mark it for stock balance deduction
                if (empty($item->purchase_request_item_id)) {
                    $productsItemWithBalDiff[] = ['id' => $item['product_id'], 'by' => $item['qty'] * -1];
                }
            }

            //purge existing items
            $requestForQuotation->items()->delete();

            foreach ($request->get('items') as $row) {
                $item = new RequestForQuotationItem;
                $item->product_id = $row['product_id'];
                $item->qty = $row['qty'];
                $item->unit_of_measure_id = $row['unit_of_measure_id'];

                if (isset($row['purchase_request_item_id'])) {
                    //if the new item has purchase request item id

                    $prItemModel = PurchaseRequestItem::findOrFail($row['purchase_request_item_id']);
                    //check the diff
                    if ($prItemModel->approved_qty != $row['qty']) {
                        $productsItemWithBalDiff[] = [
                            'id' => $row['product_id'],
                            'by' => $row['qty'] - $prItemModel->approved_qty
                        ];
                    }
                    $item->purchaseRequestItem()->associate($prItemModel);
                } else {
                    $productsItemWithBalDiff[] = ['id' => $row['product_id'], 'by' => $row['qty']];
                }

                $items[] = $item;
            }

            $requestForQuotation->items()->saveMany($items);

            $service->updateProductB2BBalance($productsItemWithBalDiff);
        }

        DB::commit();

        if ($request->boolean('download', false)) {
            //TODO generate the doc
        }

        return ['data' => $requestForQuotation];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param RequestForQuotation $requestForQuotation
     * @return Response
     */
    public function destroy(RequestForQuotation $requestForQuotation): Response
    {

        $requestForQuotation->delete();

        return \response()->noContent();
    }
}
