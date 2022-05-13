<?php

namespace App\Http\Controllers\MRF;

use App\Http\Controllers\Controller;
use App\Http\Requests\MRF\StoreApprovalRequest;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use App\Models\MaterialRequisitionItem;
use App\Models\User;
use App\Notifications\MaterialRequisition\ApprovedNotification;
use App\Notifications\MaterialRequisition\IssuanceRequestNotification;
use App\Services\MaterialRequisitionService;
use App\Utils\MRFUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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
        $this->authorize('viewAnyPendingApproval', MaterialRequisition::class);

        $meta = $this->queryMeta(['created_at', 'id'], ['items', 'activities', 'latestActivity',
            'items.worksheet']);

        $stage = MRFUtils::stage()['VERIFIED_OKAYED'];

        return MaterialRequisition::with($meta->include)
            ->whereRelation('latestActivity', 'stage', $stage)
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreApprovalRequest $request
     * @return MaterialRequisition[]|\Illuminate\Http\Response
     */
    public function store(StoreApprovalRequest       $request, MaterialRequisition $materialRequisition,
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
            $model->approved_qty = $row['approved_qty'];
            $model->update();

            if ($row['approved_qty'] > 0) {
                $hasOkayedQty = true;
            }
            if ($model->approved_qty < $model->verified_qty) {
                $rejected[] = $model;
            }

        }

        $stage = $hasOkayedQty ? MRFUtils::stage()['APPROVAL_OKAYED'] :
            MRFUtils::stage()['APPROVAL_REJECTED'];

        $activity = new MaterialRequisitionActivity;
        $activity->stage = $stage;
        $activity->request()->associate($materialRequisition);
        $activity->outcome = MRFUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks');
        $activity->save();

        $materialRequisitionService->OnApprovalFormQtyRejected($rejected);
        DB::commit();

        if ($hasOkayedQty) {
            //notify issuer
            Notification::send(User::whereNot('id', Auth::id())->MRFIssuer()->get(),
                new IssuanceRequestNotification($materialRequisition));
        }
        //notify requester
        Notification::send($materialRequisition->createdBy,
            new ApprovedNotification($materialRequisition, !$hasOkayedQty));

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
        $this->authorize('approve', $materialRequisition);

        $stage = MRFUtils::stage()['VERIFIED_OKAYED'];

        if ($materialRequisition->latestActivity->stage != $stage) {
            return \response()->noContent(404);
        }

        $meta = $this->queryMeta([], ['items', 'activities','items.worksheet']);
        $materialRequisition->load($meta->include);

        return ['data' => $materialRequisition];
    }


}
