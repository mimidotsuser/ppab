<?php

namespace App\Http\Controllers\PR;

use App\Http\Controllers\Controller;
use App\Http\Requests\PR\StoreVerificationRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestActivity;
use App\Services\PurchaseRequestService;
use App\Utils\PurchaseRequestUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     * @throws AuthorizationException
     */
    public function index(): LengthAwarePaginator
    {
        $this->authorize('viewAnyPendingVerification', PurchaseRequest::class);

        $meta = $this->queryMeta(['created_at', 'id', 'warehouse_id'],
            ['items', 'activities', 'latestActivity']);

        $stage = PurchaseRequestUtils::stage()['REQUEST_CREATED'];

        return PurchaseRequest::with($meta->include)
            ->whereRelation('latestActivity', 'stage', $stage)
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreVerificationRequest $request
     * @param PurchaseRequest $purchaseRequest
     * @param PurchaseRequestService $service
     * @return PurchaseRequest[]
     */
    public function store(StoreVerificationRequest $request, PurchaseRequest $purchaseRequest,
                          PurchaseRequestService   $service)
    {
        DB::beginTransaction();

        $itemModels = $purchaseRequest->items;
        $rejected = [];
        $hasOkayedQty = false;

        foreach ($itemModels as $model) {
            $row = Arr::first($request->get('items'), fn($row) => $row['id'] == $model->id);

            if (empty($row)) {
                $model->verified_qty = 0; //assume it was rejected
            } else {
                $model->verified_qty = $row['verified_qty'];
            }

            $model->update();
            $model->refresh();

            if ($row['verified_qty'] > 0) {
                $hasOkayedQty = true;
            }
            //if some qty was rejected
            if ($model->verified_qty < $model->requested_qty) {
                $rejected[] = $model;
            }
        }

        $stage = $hasOkayedQty ? PurchaseRequestUtils::stage()['VERIFIED_OKAYED'] :
            PurchaseRequestUtils::stage()['VERIFIED_REJECTED'];

        $activity = new PurchaseRequestActivity;
        $activity->request()->associate($purchaseRequest);
        $activity->remarks = $request->get('remarks');
        $activity->stage = $stage;
        $activity->outcome = PurchaseRequestUtils::outcome()[$stage];
        $activity->save();

        $service->OnVerificationFormQtyRejected($rejected);

        DB::commit();

        if ($hasOkayedQty) {
            //notify issuer
        }
        //notify requester

        return ['data' => $purchaseRequest];
    }

    /**
     * Display the specified resource.
     *
     * @param PurchaseRequest $purchaseRequest
     * @return PurchaseRequest[]
     * @throws AuthorizationException
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('verify', $purchaseRequest);

        $meta = $this->queryMeta([],
            ['items', 'activities', 'items.product.balance', 'latestActivity']);

        $purchaseRequest->load($meta->include);
        return ['data' => $purchaseRequest];

    }


}
