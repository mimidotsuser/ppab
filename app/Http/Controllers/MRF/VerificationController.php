<?php

namespace App\Http\Controllers\MRF;

use App\Http\Controllers\Controller;
use App\Http\Requests\MRF\StoreVerificationRequest;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use App\Models\MaterialRequisitionItem;
use App\Models\User;
use App\Notifications\MRFApprovalRequestedNotification;
use App\Notifications\MRFVerifiedNotification;
use App\Services\MaterialRequisitionService;
use App\Utils\MRFUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\ArrayShape;

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
        $this->authorize('viewAnyPendingVerification', MaterialRequisition::class);

        $meta = $this->queryMeta(['created_at', 'id'], ['items', 'activities', 'latestActivity',
            'items.worksheet']);

        $stage = MRFUtils::stage()['REQUEST_CREATED'];

        return MaterialRequisition::with($meta->include)
            ->whereRelation('latestActivity', 'stage', $stage)
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreVerificationRequest $request
     * @param MaterialRequisition $materialRequisition
     * @return \Illuminate\Http\Response|string[]
     */
    public function store(StoreVerificationRequest   $request, MaterialRequisition $materialRequisition,
                          MaterialRequisitionService $materialRequisitionService)
    {

        $stage = MRFUtils::stage()['VERIFIED_OKAYED'];

        if ($materialRequisition->latestActivity->stage != $stage) {
            return \response()->noContent(404);
        }

        $rejected = [];
        $hasOkayedQty = false;

        DB::beginTransaction();
        //update quantity verified
        foreach ($request->get('items') as $row) {
            $model = MaterialRequisitionItem::findOrFail($row['id']);
            $model->verified_qty = $row['verified_qty'];
            $model->update();

            if ($row['verified_qty'] > 0) {
                $hasOkayedQty = true;
            }
            if ($model->verified_qty < $model->requested_qty) {
                $rejected[] = $model;
            }
        }

        //create activity log
        $stage = $hasOkayedQty ? MRFUtils::stage()['VERIFIED_OKAYED'] :
            MRFUtils::stage()['VERIFIED_REJECTED'];

        $activity = new MaterialRequisitionActivity;
        $activity->stage = $stage;
        $activity->request()->associate($materialRequisition);
        $activity->outcome = MRFUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks');
        $activity->save();

        $materialRequisitionService->OnVerificationFormQtyRejected($rejected);

        DB::commit();

        if ($hasOkayedQty) {
            //notify approvers
            Notification::send(User::whereNot('id', Auth::id())->MRFApprover()->get(),
                new MRFApprovalRequestedNotification($materialRequisition));
        }
        //notify requester
        Notification::send($materialRequisition->createdBy,
            new MRFVerifiedNotification($materialRequisition, !$hasOkayedQty));

        return ['data' => $materialRequisition];
    }

    /**
     * Display the specified resource.
     *
     * @param MaterialRequisition $materialRequisition
     * @return MaterialRequisition[]|array|\Illuminate\Http\Response
     * @throws AuthorizationException
     */
    #[ArrayShape(['data' => "\App\Models\MaterialRequisition"])]
    public function show(MaterialRequisition $materialRequisition)
    {
        $this->authorize('verify', $materialRequisition);

        $stage = MRFUtils::stage()['VERIFIED_OKAYED'];

        if ($materialRequisition->latestActivity->stage != $stage) {
            return \response()->noContent(404);
        }

        $meta = $this->queryMeta([], ['items', 'activities', 'items.worksheet']);
        $materialRequisition->load($meta->include);

        return ['data' => $materialRequisition];
    }


}
