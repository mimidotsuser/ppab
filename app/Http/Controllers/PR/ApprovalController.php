<?php

namespace App\Http\Controllers\PR;

use App\Http\Controllers\Controller;
use App\Http\Requests\PR\StoreApprovalRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestActivity;
use App\Notifications\PurchaseRequest\ApprovedNotification;
use App\Services\PurchaseRequestService;
use App\Utils\PurchaseRequestUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\ArrayShape;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        $this->authorize('viewAnyPendingApproval', PurchaseRequest::class);

        $meta = $this->queryMeta(['created_at', 'id', 'warehouse_id'],
            ['items', 'activities', 'latestActivity']);

        $stage = PurchaseRequestUtils::stage()['VERIFIED_OKAYED'];

        return PurchaseRequest::with($meta->include)
            ->whereRelation('latestActivity', 'stage', $stage)
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreApprovalRequest $request
     * @param PurchaseRequest $purchaseRequest
     * @param PurchaseRequestService $service
     * @return PurchaseRequest[]|array|\Illuminate\Http\Response
     */
    public function store(StoreApprovalRequest   $request, PurchaseRequest $purchaseRequest,
                          PurchaseRequestService $service)
    {

        $stage = PurchaseRequestUtils::stage()['VERIFIED_OKAYED'];

        if ($purchaseRequest->latestActivity->stage != $stage) {
            return \response()->noContent(404);
        }


        DB::beginTransaction();

        $itemModels = $purchaseRequest->items;
        $rejected = [];
        $hasOkayedQty = false;

        foreach ($itemModels as $model) {
            $row = Arr::first($request->get('items'), fn($row) => $row['id'] == $model->id);

            if (empty($row)) {
                $model->approved_qty = 0; //assume it was rejected
            } else {
                $model->approved_qty = $row['approved_qty'];
            }

            $model->update();
            $model->refresh();

            if ($row['approved_qty'] > 0) {
                $hasOkayedQty = true;
            }
            //if some qty was rejected
            if ($model->approved_qty < $model->verified_qty) {
                $rejected[] = $model;
            }
        }

        $stage = $hasOkayedQty ? PurchaseRequestUtils::stage()['APPROVAL_OKAYED'] :
            PurchaseRequestUtils::stage()['APPROVAL_REJECTED'];

        $activity = new PurchaseRequestActivity;
        $activity->request()->associate($purchaseRequest);
        $activity->remarks = $request->get('remarks');
        $activity->stage = $stage;
        $activity->outcome = PurchaseRequestUtils::outcome()[$stage];
        $activity->save();

        $service->OnApprovalFormQtyRejected($rejected);

        DB::commit();

        //notify requester
        Notification::send($purchaseRequest->createdBy,
            new ApprovedNotification($purchaseRequest, !$hasOkayedQty));

        return ['data' => $purchaseRequest];
    }

    /**
     * Display the specified resource.
     *
     * @param PurchaseRequest $purchaseRequest
     * @return PurchaseRequest[]|array|\Illuminate\Http\Response
     * @throws AuthorizationException
     */
    #[ArrayShape(['data' => "\App\Models\PurchaseRequest"])]
    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('approve', $purchaseRequest);

        $stage = PurchaseRequestUtils::stage()['VERIFIED_OKAYED'];

        if ($purchaseRequest->latestActivity->stage != $stage) {
            return \response()->noContent(404);
        }

        $meta = $this->queryMeta([],
            ['items', 'activities', 'items.product.balance', 'latestActivity']);

        $purchaseRequest->load($meta->include);

        return ['data' => $purchaseRequest];
    }

}
