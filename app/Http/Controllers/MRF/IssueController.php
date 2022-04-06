<?php

namespace App\Http\Controllers\MRF;

use App\Http\Controllers\Controller;
use App\Http\Requests\MRF\StoreIssueRequest;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use App\Models\MaterialRequisitionItem;
use App\Models\ProductItemActivity;
use App\Models\ProductItemWarrant;
use App\Notifications\MRFIssuedNotification;
use App\Services\MaterialRequisitionService;
use App\Utils\MRFUtils;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\ArrayShape;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        $this->authorize('viewAny', MaterialRequisition::class);

        $meta = $this->queryMeta(['created_at', 'id'], ['items', 'activities', 'latestActivity']);

        $stage = MRFUtils::stage()['APPROVAL_OKAYED'];
        $partialIssued = MRFUtils::stage()['PARTIAL_ISSUE'];

        return MaterialRequisition::with($meta->include)
            ->whereRelation('latestActivity', 'stage', $stage)
            ->orWhereRelation('latestActivity', 'stage', $partialIssued)
            ->paginate($meta->limit, '*', 'page', $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreIssueRequest $request
     * @param MaterialRequisition $materialRequisition
     * @param MaterialRequisitionService $requisitionService
     * @return array
     */
    public function store(StoreIssueRequest          $request, MaterialRequisition $materialRequisition,
                          MaterialRequisitionService $requisitionService): array
    {

        /**
         * 1. update item issued qty
         * 2. For each machine,:
         *      - create a warrant item if data exists
         *      - add activity to show its current location
         * 3. create MRF activity log
         */
        DB::beginTransaction();

        //1) spares
        if (!empty($request->get('items')['spares'])) {
            $sparesIssued = $request->get('items')['spares'];

            //use whereIn to reduce database load
            $ids = Arr::pluck($sparesIssued, 'id');
            $itemModels = MaterialRequisitionItem::whereIn('id', $ids)->get();

            foreach ($itemModels as $itemModel) {
                //will raise error if item is not found
                $item = Arr::first($sparesIssued, fn($v) => $v['id'] == $itemModel->id);

                $qty = $item['old_total'] + $item['new_total'];

                $itemModel->issued_qty = $itemModel->issued_qty ? $itemModel->issued_qty + $qty : $qty;
                $itemModel->update();

                //call unrelated logic. (Done here to reduce database calls)
                $requisitionService->OnSpareIssue($itemModel->product, $item['new_total'], $item['old_total']);
            }
        }
        //2) machines
        if (!empty($request->get('items')['machines'])) {
            $machinesIssued = $request->get('items')['machines'];

            //use whereIn to reduce database load
            $ids = Arr::pluck($machinesIssued, 'id');
            $itemModels = MaterialRequisitionItem::whereIn('id', $ids)->get();

            foreach ($itemModels as $itemModel) {
                $item = Arr::first($machinesIssued, fn($item) => $item['id'] == $itemModel->id);

                $qty = count($item['allocation']);

                $itemModel->issued_qty = $itemModel->issued_qty ? $qty + $itemModel->issued_qty : $qty;
                $itemModel->update();

                foreach ($item['allocation'] as $allotment) {
                    $category_code = 'MATERIAL_REQUISITION_ISSUED';
                    $category_title = ProductItemActivityUtils::activityCategories()[$category_code];

                    $productItemActivity = new ProductItemActivity;
                    $productItemActivity->log_category_code = $category_code;
                    $productItemActivity->log_category_title = $category_title;

                    $productItemActivity->product_item_id = $allotment['product_item_id'];
                    $productItemActivity->location()->associate($itemModel->customer);

                    // create a warrant item
                    if ($allotment['warrant_start']) {
                        $warranty = new ProductItemWarrant;
                        $warranty->product_item_id = $allotment['product_item_id'];
                        $warranty->customer()->associate($itemModel->customer);
                        $warranty->warrant_start = $allotment['warrant_start'];
                        $warranty->warrant_end = $allotment['warrant_end'];
                        $warranty->save();

                        $productItemActivity->warrant()->associate($warranty);
                    }
                    $productItemActivity->eventable()->associate($materialRequisition);
                    $productItemActivity->save();
                }

                //call unrelated logic [called here to reduce fetching data again]
                $requisitionService->OnMachineIssue($itemModel->product, $qty);
            }
        }

        //3) create the activity log

        //check if it was a partial issue
        $hasPartiallyIssued = $materialRequisition->items()
            ->whereRaw('COALESCE(approved_qty,0)-COALESCE(issued_qty,0)>0')
            ->exists();

        $stage = $hasPartiallyIssued ? MRFUtils::stage()['PARTIAL_ISSUE'] : MRFUtils::stage()['ISSUED'];

        $activity = new MaterialRequisitionActivity;
        $activity->stage = $stage;
        $activity->request()->associate($materialRequisition);
        $activity->outcome = MRFUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks');
        $activity->save();

        DB::commit();

        //emit email notification to user
        //notify requester
        Notification::send(Auth::user(),
            new MRFIssuedNotification($materialRequisition, $hasPartiallyIssued == 0));

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
        $this->authorize('issue', $materialRequisition);

        $meta = $this->queryMeta([], ['items', 'activities']);
        $materialRequisition->load($meta->include);

        return ['data' => $materialRequisition];
    }

}
