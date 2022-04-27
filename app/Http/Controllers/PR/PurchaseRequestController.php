<?php

namespace App\Http\Controllers\PR;

use App\Actions\GeneratePRDoc;
use App\Http\Controllers\Controller;
use App\Http\Requests\PR\StorePurchaseRequisitionRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestActivity;
use App\Models\PurchaseRequestItem;
use App\Services\PurchaseRequestService;
use App\Utils\PurchaseRequestUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class PurchaseRequestController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(PurchaseRequest::class, 'purchase_request',
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
            $this->authorize('search', PurchaseRequest::class);
        } else {
            $this->authorize('viewAny', PurchaseRequest::class);
        }

        $meta = $this->queryMeta(['created_at', 'id', 'warehouse_id'],
            ['items', 'activities', 'latestActivity', 'items.product.balance']);

        return PurchaseRequest::with($meta->include)
            ->when($request->search, function ($query, string $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);
                });
            })
            ->when($request->boolean('withoutRFQ', false), function (Builder $query) {
                $query->doesntHave('rfq');
            })
            ->when($request->get('stage'), function (Builder $query, string $stage,) {
                $query->whereRelation('latestActivity', 'stage', $stage);
            })
            ->when($request->get('stages'), function (Builder $builder, $stages) {

                $builder->whereHas('latestActivity', function ($query) use ($stages) {
                    $query->where(function ($query) use ($stages) {
                        $parsedStages = explode(',', $stages);
                        foreach ($parsedStages as $stage) {

                            if ($stage === 'approved') {
                                $query->orWhere('stage', PurchaseRequestUtils::stage()['APPROVAL_OKAYED']);
                            }
                            if ($stage === 'verified') {
                                $query->orWhere('stage', PurchaseRequestUtils::stage()['VERIFIED_OKAYED']);
                            }
                            if ($stage === 'created') {
                                $query->orWhere('stage', PurchaseRequestUtils::stage()['REQUEST_CREATED']);
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
     * @param StorePurchaseRequisitionRequest $request
     */
    public function store(StorePurchaseRequisitionRequest $request, PurchaseRequestService $service): array
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
     * @return PurchaseRequest[]|Response
     */
    #[ArrayShape(['data' => "\App\Models\PurchaseRequest"])]
    public function show(PurchaseRequest $purchaseRequest)
    {

        if (\request()->boolean('withoutRFQ', false)
            && $purchaseRequest->has('rfq')->exists()) {
            return \response()->noContent(404);
        }
        $meta = $this->queryMeta([], ['items', 'activities', 'latestActivity',
            'items.product.balance']);

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

    public function downloadPurchaseRequestDoc(PurchaseRequest $purchaseRequest, GeneratePRDoc $PRFile)
    {
        $verificationStage = PurchaseRequestUtils::stage()['VERIFIED_OKAYED'];
        $approvalStage = PurchaseRequestUtils::stage()['APPROVAL_OKAYED'];


        $purchaseRequest->load(['items.product.balance', 'createdBy', 'activities' => function ($query) {
            $query->latest();
        }]);


        $verification = $purchaseRequest->activities->firstWhere('stage', $verificationStage);
        $approval = $purchaseRequest->activities->firstWhere('stage', $approvalStage);


        return $PRFile($purchaseRequest, $verification, $approval);
    }
}
