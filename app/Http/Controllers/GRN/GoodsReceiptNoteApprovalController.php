<?php

namespace App\Http\Controllers\GRN;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteActivity;
use App\Notifications\GoodsReceivedNote\ApprovedNotification;
use App\Services\GoodsReceiptNoteService;
use App\Utils\GoodsReceiptNoteUtils;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use function response;

class GoodsReceiptNoteApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     * @throws AuthorizationException
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $this->authorize('viewAnyPendingApproval', GoodsReceiptNote::class);

        $meta = $this->queryMeta(['created_at', 'sn'],
            ['createdBy', 'updatedBy', 'items', 'latestActivity', 'purchaseOrder']);

        $stage = GoodsReceiptNoteUtils::stage()['INSPECTION_DONE'];

        return GoodsReceiptNote::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);
                });
            })
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->whereRelation('latestActivity', 'stage', $stage)
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param GoodsReceiptNote $goodsReceiptNote
     * @param GoodsReceiptNoteService $service
     * @return array|\Illuminate\Http\Response
     */
    public function store(Request                 $request, GoodsReceiptNote $goodsReceiptNote,
                          GoodsReceiptNoteService $service)
    {
        $this->authorize('approve', $goodsReceiptNote);

        $stage = GoodsReceiptNoteUtils::stage()['INSPECTION_DONE'];

        if ($goodsReceiptNote->latestActivity->stage != $stage) {
            return response()->noContent(404);
        }

        $request->validate([
            'remarks' => 'nullable|max::255',
            'approved' => 'required|boolean'
        ]);

        $stage = GoodsReceiptNoteUtils::stage()[$request->boolean('approved', true) ?
            'APPROVAL_OKAYED' : 'APPROVAL_REJECTED'];
        DB::beginTransaction();
        $activity = new GoodsReceiptNoteActivity;
        $activity->stage = $stage;
        $activity->outcome = GoodsReceiptNoteUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks');
        $activity->request()->associate($goodsReceiptNote);
        $activity->save();

        //if not approved, we will not touch the stock balances
        if ($request->boolean('approved', true)) {
            $service->syncB2BToQtyInBalance($goodsReceiptNote);
        }

        DB::commit();


        //notify requester
        Notification::send($goodsReceiptNote->createdBy,
            new ApprovedNotification($goodsReceiptNote,
                $request->boolean('approved', true)));

        return ['data' => $goodsReceiptNote];
    }

    /**
     * Display the specified resource.
     *
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return array|\Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function show(GoodsReceiptNote $goodsReceiptNote)
    {
        $this->authorize('approve', $goodsReceiptNote);
        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'items', 'activities',
            'purchaseOrder', 'InspectionNote', 'items.product']);
        $stage = GoodsReceiptNoteUtils::stage()['INSPECTION_DONE'];

        if ($goodsReceiptNote->latestActivity->stage != $stage) {
            return response()->noContent(404);
        }

        $goodsReceiptNote->load($meta->include);
        return ['data' => $goodsReceiptNote];
    }

    public function update(Request                 $request, GoodsReceiptNote $goodsReceiptNote,
                           GoodsReceiptNoteService $service)
    {
        $this->authorize('approve', $goodsReceiptNote);

        $request->validate([
            'remarks' => 'nullable|max::255',
            'approved' => 'required|boolean'
        ]);

        $lastActivity = $goodsReceiptNote->latestActivity;

        $stage = GoodsReceiptNoteUtils::stage()[$request->boolean('approved', true) ?
            'APPROVAL_OKAYED' : 'APPROVAL_REJECTED'];
        DB::beginTransaction();
        $activity = new GoodsReceiptNoteActivity;
        $activity->stage = $stage;
        $activity->outcome = GoodsReceiptNoteUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks');
        $activity->request()->associate($goodsReceiptNote);
        $activity->save();


        if ($lastActivity->stage != $stage) { //if old decision is same, do nothing

            if ($request->boolean('approved', true)) {
                //if new decision approves, move-(synced) B2B to Qty In
                $service->syncB2BToQtyInBalance($goodsReceiptNote);
            } else {
                //if the new decision rejects, move-(synced) Qty In to B2B
                $service->syncQtyInBalanceToB2B($goodsReceiptNote);
            }
        }

        DB::commit();

        return ['data' => $goodsReceiptNote];
    }


    public function destroy(GoodsReceiptNote $goodsReceiptNote, GoodsReceiptNoteService $service)
    {
        $this->authorize('approve', $goodsReceiptNote);

        DB::beginTransaction();
        $service->syncQtyInBalanceToB2B($goodsReceiptNote);
        $goodsReceiptNote->delete();

        DB::commit();

        return response()->noContent();
    }

}
