<?php

namespace App\Http\Controllers\MRF;

use App\Http\Controllers\Controller;
use App\Http\Requests\MRF\StoreMaterialRequisitionRequest;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use App\Models\MaterialRequisitionItem;
use App\Services\MaterialRequisitionService;
use App\Utils\MRFUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use function response;

class MaterialRequisitionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MaterialRequisition::class, 'material_requisition');
    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'id'], ['items', 'activities', 'latestActivity']);

        return MaterialRequisition::with($meta->include)
            ->paginate($meta->limit, '*', 'page', $meta->page);
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
        $mrf->save();


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
        //notify requester

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
        $meta = $this->queryMeta([], ['items', 'activities', 'latestActivity']);

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
}
