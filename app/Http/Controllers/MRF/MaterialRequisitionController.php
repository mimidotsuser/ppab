<?php

namespace App\Http\Controllers\MRF;

use App\Actions\GenerateMRNDoc;
use App\Actions\GenerateSIVDoc;
use App\Http\Controllers\Controller;
use App\Http\Requests\MRF\StoreMaterialRequisitionRequest;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use App\Models\MaterialRequisitionItem;
use App\Models\User;
use App\Notifications\MRFCreatedNotification;
use App\Notifications\MRFVerificationRequestedNotification;
use App\Services\MaterialRequisitionService;
use App\Utils\MRFUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class MaterialRequisitionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MaterialRequisition::class, 'material_requisition',
            ['except' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        if ($request->search) {
            $this->authorize('search', MaterialRequisition::class);
        } else {
            $this->authorize('viewAny', MaterialRequisition::class);
        }

        $meta = $this->queryMeta(['created_at', 'id'], ['items', 'activities', 'latestActivity',
            'items.product', 'items.product.variants', 'items.worksheet', 'balanceActivities']);

        return MaterialRequisition::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {

                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);

                    $query->orWhereRelationBeginsWith('createdBy', 'first_name', $searchTerm);
                    $query->orWhereRelationLike('createdBy', 'first_name', $searchTerm);

                    $query->orWhereRelationBeginsWith('createdBy', 'last_name', $searchTerm);
                    $query->orWhereRelationLike('createdBy', 'last_name', $searchTerm);

                });

            })
            ->when($request->get('stage'), function ($builder, $stage) {

                if ($stage === 'issued') {
                    $stage = MRFUtils::stage()['ISSUED'];
                    $partialIssued = MRFUtils::stage()['PARTIAL_ISSUE'];

                    $builder->whereRelation('latestActivity', 'stage', $stage);
                    $builder->orWhereRelation('latestActivity', 'stage', $partialIssued);
                }
                if ($stage === 'approved') {
                    $builder->whereRelation('latestActivity', 'stage', MRFUtils::stage()['APPROVAL_OKAYED']);
                }
                if ($stage === 'verified') {
                    $builder->whereRelation('latestActivity', 'stage', MRFUtils::stage()['VERIFIED_OKAYED']);
                }
                if ($stage === 'created') {
                    $builder->whereRelation('latestActivity', 'stage', MRFUtils::stage()['REQUEST_CREATED']);
                }

            })
            ->when($request->get('stages'), function (Builder $builder, $stages) {

                $builder->whereHas('latestActivity', function ($query) use ($stages) {
                    $query->where(function ($query) use ($stages) {
                        $parsedStages = explode(',', $stages);
                        foreach ($parsedStages as $stage) {
                            if ($stage === 'issued') {
                                $query->orWhere('stage', MRFUtils::stage()['PARTIAL_ISSUE']);
                                $query->orWhere('stage', MRFUtils::stage()['ISSUED']);
                            }
                            if ($stage === 'approved') {
                                $query->orWhere('stage', MRFUtils::stage()['APPROVAL_OKAYED']);
                            }
                            if ($stage === 'verified') {
                                $query->orWhere('stage', MRFUtils::stage()['VERIFIED_OKAYED']);
                            }
                            if ($stage === 'created') {
                                $query->orWhere('stage', MRFUtils::stage()['REQUEST_CREATED']);
                            }
                        }
                    });
                });
            })
            ->when($request->get('start_date'), function (Builder $builder, $startDate) {
                $builder->whereDate('created_at', '>', $startDate);
            })
            ->when($request->date('end_date'), function (Builder $builder, $endDate) {
                $builder->whereDate('created_at', '<', $endDate);
            })
            ->when($request->get('created_by'), function (Builder $builder, $authorIds) {
                $builder->whereIn('created_by_id', explode(',', $authorIds));
            })
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreMaterialRequisitionRequest $request
     * @return MaterialRequisition[]
     */
    #[ArrayShape(['data' => "\App\Models\MaterialRequisition"])]
    public function store(StoreMaterialRequisitionRequest $request,
                          MaterialRequisitionService      $requisitionService): array
    {
        DB::beginTransaction();
        $mrf = new MaterialRequisition;
        $mrf->warehouse_id = $request->get('warehouse_id');
        $mrf->email_thread_id = Str::replace('-', '', (string)Str::uuid());
        $mrf->save();
        $mrf->refresh();


        foreach ($request->get('items') as $row) {
            $purposeCode = $row['purpose_code'];
            $purposeTitle = MRFUtils::purpose()[$purposeCode];

            $item = new MaterialRequisitionItem;
            $item->request()->associate($mrf);
            $item->product_id = $row['product_id'];
            $item->customer_id = $row['customer_id'];
            $item->worksheet_id = $row['worksheet_id'] ?? null;
            $item->purpose_code = $purposeCode;
            $item->purpose_title = $purposeTitle;
            $item->requested_qty = $row['requested_qty'];

            $item->save();
        }

        $activity = new MaterialRequisitionActivity;
        $activity->request()->associate($mrf);
        $activity->remarks = $request->get('remarks') ?? 'Request created';
        $activity->stage = MRFUtils::stage()['REQUEST_CREATED'];
        $activity->outcome = MRFUtils::outcome()['REQUEST_CREATED'];
        $activity->save();

        $requisitionService->OnFormCreate($request->get('items'));

        DB::commit();

        //notify verifiers
        Notification::send(User::whereNot('id', Auth::id())->MRFVerifier()->get(),
            new MRFVerificationRequestedNotification($mrf));
        //notify requester
        Notification::send(Auth::user(), new MRFCreatedNotification($mrf));

        return ['data' => $mrf];
    }

    /**
     * Display the specified resource.
     *
     * @param MaterialRequisition $materialRequisition
     * @return MaterialRequisition[]
     */
    #[ArrayShape(['data' => "\App\Models\MaterialRequisition"])]
    public function show(MaterialRequisition $materialRequisition): array
    {
        $meta = $this->queryMeta([], ['items', 'activities', 'latestActivity', 'items.product',
            'items.product.variants', 'balanceActivities', 'items.worksheet']);

        $materialRequisition->load($meta->include);
        return ['data' => $materialRequisition];
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param MaterialRequisition $materialRequisition
     * @return Response
     */
    public function destroy(MaterialRequisition $materialRequisition): Response
    {
        $materialRequisition->delete();
        return response()->noContent();
    }

    /**
     * @param MaterialRequisition $materialRequisition
     * @param GenerateMRNDoc $MRNFile
     * @return Response|void
     * @throws AuthorizationException
     */
    public function downloadMaterialRequisitionNote(MaterialRequisition $materialRequisition,
                                                    GenerateMRNDoc      $MRNFile)
    {
        $this->authorize('view', $materialRequisition);

        //allow only approved

        $verificationStage = MRFUtils::stage()['VERIFIED_OKAYED'];
        $approvedStage = MRFUtils::stage()['APPROVAL_OKAYED'];
        $partiallyIssuedStage = MRFUtils::stage()['PARTIAL_ISSUE'];
        $issuedStage = MRFUtils::stage()['ISSUED'];

        $stage = $materialRequisition->latestActivity->stage;
        if ($stage != $approvedStage && $stage != $partiallyIssuedStage && $stage != $issuedStage) {
            return response()->noContent(404);
        }
        $materialRequisition->load(['items', 'createdBy', 'activities' => function ($query) {
            $query->latest();
        }]);

        $verification = $materialRequisition->activities->firstWhere('stage', $verificationStage);
        $approval = $materialRequisition->activities->firstWhere('stage', $approvedStage);

        $MRNFile($materialRequisition, $verification, $approval)
            ->stream('mrn-' . strtolower($materialRequisition->sn) . ".pdf");
    }

    /**
     * @param MaterialRequisition $materialRequisition
     * @param GenerateSIVDoc $SIVFile
     * @return Response|void
     * @throws AuthorizationException
     */
    public function downloadStoreIssueNote(MaterialRequisition $materialRequisition,
                                           GenerateSIVDoc      $SIVFile)
    {
        $this->authorize('view', $materialRequisition);
        //allow only approved

        $partiallyIssuedStage = MRFUtils::stage()['PARTIAL_ISSUE'];
        $issuedStage = MRFUtils::stage()['ISSUED'];

        $stage = $materialRequisition->latestActivity->stage;
        if ($stage != $partiallyIssuedStage && $stage != $issuedStage) {
            return response()->noContent(404);
        }
        $materialRequisition->load(['items', 'createdBy', 'activities' => function ($query) {
            $query->latest();
        }]);

        $issue = $materialRequisition->activities->firstWhere('stage', $issuedStage);
        if (empty($issue)) {
            $issue = $materialRequisition->activities->firstWhere('stage', $partiallyIssuedStage);
        }

        $approvedStage = MRFUtils::stage()['APPROVAL_OKAYED'];
        $approval = $materialRequisition->activities->firstWhere('stage', $approvedStage);

        $SIVFile($materialRequisition, $approval, $issue)
            ->stream('siv-' . strtolower($materialRequisition->sn) . ".pdf");
    }
}
