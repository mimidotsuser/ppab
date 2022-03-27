<?php

namespace App\Http\Controllers\MRF;

use App\Http\Controllers\Controller;
use App\Http\Requests\MRF\StoreVerificationRequest;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use App\Models\MaterialRequisitionItem;
use App\Services\MaterialRequisitionService;
use App\Utils\MRFUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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
        $this->authorize('viewAny', MaterialRequisition::class);

        $meta = $this->queryMeta(['created_at', 'id'], ['items', 'activities', 'latestActivity']);

        $stage = MRFUtils::stage()['REQUEST_CREATED'];

        return MaterialRequisition::with($meta->include)
            ->whereRelation('latestActivity', 'stage', $stage)
            ->paginate($meta->limit, '*', 'page', $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreVerificationRequest $request
     * @param MaterialRequisition $materialRequisition
     * @return string[]
     */
    public function store(StoreVerificationRequest   $request, MaterialRequisition $materialRequisition,
                          MaterialRequisitionService $materialRequisitionService)
    {
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

        return ['data' => $materialRequisition];
    }

    /**
     * Display the specified resource.
     *
     * @param MaterialRequisition $materialRequisition
     * @return MaterialRequisition[]
     * @throws AuthorizationException
     */
    #[ArrayShape(['data' => "\App\Models\MaterialRequisition"])]
    public function show(MaterialRequisition $materialRequisition): array
    {
        $this->authorize('view', $materialRequisition);

        $meta = $this->queryMeta([], ['items', 'activities']);
        $materialRequisition->load($meta->include);

        return ['data' => $materialRequisition];
    }


}