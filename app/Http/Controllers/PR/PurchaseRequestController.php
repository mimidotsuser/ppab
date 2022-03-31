<?php

namespace App\Http\Controllers\PR;

use App\Http\Controllers\Controller;
use App\Http\Requests\PR\StorePurchaseRequestRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestActivity;
use App\Models\PurchaseRequestItem;
use App\Services\PurchaseRequestService;
use App\Utils\PurchaseRequestUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class PurchaseRequestController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(PurchaseRequest::class, 'purchase_request');
    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'id', 'warehouse_id'],
            ['items', 'activities', 'latestActivity']);
        return PurchaseRequest::with($meta->include)
            ->paginate($meta->limit, '*', 'page', $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePurchaseRequestRequest $request
     * @return PurchaseRequest[]
     */
    public function store(StorePurchaseRequestRequest $request,PurchaseRequestService $service)
    {
        DB::beginTransaction();

        $pr = new PurchaseRequest;
        $pr->warehouse_id = $request->get('warehouse_id');
        $pr->email_thread_id = Str::replace('-', '', (string)Str::uuid());
        $pr->save();
        $pr->refresh();

        //save items
        $items = [];
        foreach ($request->get('items') as $row) {
            $item = new PurchaseRequestItem;
            $item->requested_qty = $row['requested_qty'];
            $item->product_id = $row['product_id'];
            $items[] = $item;
        }
        $pr->items()->saveMany($items);

        //save activity
        $activity = new PurchaseRequestActivity;
        $activity->request()->associate($pr);
        $activity->remarks = $request->get('remarks') ?? 'Request created';
        $activity->stage = PurchaseRequestUtils::stage()['REQUEST_CREATED'];
        $activity->outcome = PurchaseRequestUtils::outcome()['REQUEST_CREATED'];
        $activity->save();

        //update B2B pipeline balance
        $service->onFormCreate($request->get('items'));

        DB::commit();

        //notify requester

        //notify verifiers

        return ['data' => $pr];
    }

    /**
     * Display the specified resource.
     *
     * @param PurchaseRequest $purchaseRequest
     * @return PurchaseRequest[]
     */
    #[ArrayShape(['data' => "\App\Models\PurchaseRequest"])]
    public function show(PurchaseRequest $purchaseRequest): array
    {

        $meta = $this->queryMeta([], ['items', 'activities', 'latestActivity']);

        $purchaseRequest->load($meta->include);
        return ['data' => $purchaseRequest];
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param PurchaseRequest $purchaseRequest
     * @return Response
     */
    public function destroy(PurchaseRequest $purchaseRequest): Response
    {
        $purchaseRequest->delete();
        return response()->noContent();
    }
}
