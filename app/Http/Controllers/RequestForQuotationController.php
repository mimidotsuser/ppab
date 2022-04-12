<?php

namespace App\Http\Controllers;

use App\Actions\GenerateRFQDocs;
use App\Http\Requests\StoreRequestForQuotationRequest;
use App\Http\Requests\UpdateRequestForQuotationRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RequestForQuotation;
use App\Models\RequestForQuotationItem;
use App\Models\UnitOfMeasure;
use App\Services\RequestForQuotationService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'items', 'vendors', 'purchaseRequest', 'purchaseOrder', 'items.product.balance']);

        return RequestForQuotation::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);
                });
            })
            ->when($request->boolean('withoutPO', false), function (Builder $query) {
                $query->doesntHave('purchaseOrder');
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
     * @param StoreRequestForQuotationRequest $request
     * @param RequestForQuotationService $service
     * @return array
     */
    public function store(StoreRequestForQuotationRequest $request,
                          RequestForQuotationService      $service): array
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

                $uom = UnitOfMeasure::findOrFail($row['unit_of_measure_id']);

                if (isset($row['purchase_request_item_id'])) {
                    $prItemModel = PurchaseRequestItem::findOrFail($row['purchase_request_item_id']);
                    //check the diff
                    if ($prItemModel->approved_qty != ($row['qty'] * $uom->unit)) {
                        $productsItemWithBalDiff[] = [
                            'id' => $row['product_id'],
                            'by' => ($row['qty'] * $uom->unit) - $prItemModel->approved_qty
                        ];
                    }
                    $item->purchaseRequestItem()->associate($prItemModel);
                } else {
                    $productsItemWithBalDiff[] = [
                        'id' => $row['product_id'],
                        'by' => ($row['qty'] * $uom->unit)
                    ];
                }

                $items[] = $item;
            }

            $rfq->items()->saveMany($items);

            $service->updateProductB2BBalance($productsItemWithBalDiff);
        }

        DB::commit();

        return ['data' => $rfq];
    }

    /**
     * Display the specified resource.
     *
     * @param RequestForQuotation $requestForQuotation
     * @return RequestForQuotation[]|Response
     */
    public function show(RequestForQuotation $requestForQuotation)
    {

        if (\request()->boolean('withoutPO', false)
            && $requestForQuotation->has('purchaseOrder')->exists()) {
            return \response()->noContent(404);
        }
        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'items', 'vendors',
            'purchaseRequest', 'purchaseOrder', 'items.product.balance']);

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
                if (!isset($item->purchase_request_item_id)) {
                    //if it has no purchase request item id, mark it for stock balance deduction
                    $productsItemWithBalDiff[] = [
                        'id' => $item['product_id'],
                        'by' => $item['qty'] * -1 * $item->uom->unit
                    ];
                } else {
                    $model = $item->purchaseRequestItem;

                    if ($model->approved_qty != $item['qty'] * $item->uom->unit) {
                        //reset to how it was before RFQ
                        $productsItemWithBalDiff[] = [
                            'id' => $item['product_id'],
                            'by' => $model->approved_qty - ($item['qty'] * $item->uom->unit)
                        ];
                    }
                }
            }

            //purge existing items
            $requestForQuotation->items()->delete();

            foreach ($request->get('items') as $row) {
                $item = new RequestForQuotationItem;
                $item->product_id = $row['product_id'];
                $item->qty = $row['qty'];
                $item->unit_of_measure_id = $row['unit_of_measure_id'];

                $uom = UnitOfMeasure::findOrFail($row['unit_of_measure_id']);

                if (isset($row['purchase_request_item_id'])) {
                    //if the new item has purchase request item id

                    $prItemModel = PurchaseRequestItem::findOrFail($row['purchase_request_item_id']);
                    //check the diff
                    if ($prItemModel->approved_qty != $row['qty'] * $uom->unit) {
                        $productsItemWithBalDiff[] = [
                            'id' => $row['product_id'],
                            'by' => ($row['qty'] * $uom->unit) - $prItemModel->approved_qty
                        ];
                    }
                    $item->purchaseRequestItem()->associate($prItemModel);
                } else {
                    $productsItemWithBalDiff[] = [
                        'id' => $row['product_id'],
                        'by' => $row['qty'] * $uom->unit
                    ];
                }

                $items[] = $item;
            }

            $requestForQuotation->items()->saveMany($items);

            $service->updateProductB2BBalance($productsItemWithBalDiff);
        }

        DB::commit();

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

        DB::beginTransaction();
        $productsItemWithBalDiff = [];

        //reset restock quantity
        foreach ($requestForQuotation->items as $item) {
            if (!isset($item->purchase_request_item_id)) {
                //if it has no purchase request item id, mark it for stock balance deduction
                $productsItemWithBalDiff[] = [
                    'id' => $item['product_id'],
                    'by' => $item['qty'] * -1 * $item->uom->unit
                ];
            } else {
                $model = $item->purchaseRequestItem;

                if ($model->approved_qty != $item['qty'] * $item->uom->unit) {
                    //reset to how it was before RFQ
                    $productsItemWithBalDiff[] = [
                        'id' => $item['product_id'],
                        'by' => $model->approved_qty - ($item['qty'] * $item->uom->unit)
                    ];
                }
            }
        }


        $requestForQuotation->delete();
        DB::commit();

        return \response()->noContent();
    }

    /**
     * @param RequestForQuotation $requestForQuotation
     * @param GenerateRFQDocs $RFQDocs
     * @return BinaryFileResponse
     * @throws AuthorizationException
     */
    public function downloadRFQDocs(RequestForQuotation $requestForQuotation,
                                    GenerateRFQDocs     $RFQDocs): BinaryFileResponse
    {
        $this->authorize('view', $requestForQuotation);

        $requestForQuotation->load(['purchaseRequest', 'items.product', 'items.uom', 'createdBy']);

        return response()
            ->download($RFQDocs($requestForQuotation),
                'rfq-' . strtolower($requestForQuotation->sn) . '.zip')
            ->deleteFileAfterSend();
    }
}
