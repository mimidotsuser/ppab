<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInspectionNoteRequest;
use App\Http\Requests\UpdateInspectionNoteRequest;
use App\Models\GoodsReceiptNoteActivity;
use App\Models\GoodsReceiptNoteItem;
use App\Models\InspectionChecklist;
use App\Models\InspectionNote;
use App\Utils\GoodsReceiptNoteUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InspectionNoteController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InspectionNote::class, 'inspection_note');
    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $meta = $this->queryMeta(['created_at', 'sn'],
            ['createdBy', 'updatedBy', 'goodsReceiptNote', 'goodsReceiptNote.purchaseOrder',
                'goodsReceiptNote.latestActivity', 'goodsReceiptNote.items']);

        return InspectionNote::with($meta->include)
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
            ->paginate($meta->limit, '*', $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInspectionNoteRequest $request
     * @return array
     */
    public function store(StoreInspectionNoteRequest $request): array
    {
        DB::beginTransaction();
        $inspectionNote = new InspectionNote;
        $inspectionNote->goods_receipt_note_id = $request->get('goods_receipt_note_id');
        $inspectionNote->remarks = $request->get('remarks');
        $inspectionNote->save();
        $inspectionNote->refresh();

        //save checklist
        if ($request->has('checklist')) {
            $checklist = [];
            foreach ($request->checklist as $row) {
                $item = new InspectionChecklist;
                $item->feature = $row['feature'];
                $item->passed = $row['passed'];
                $checklist[] = $item;
            }
            $inspectionNote->checklist()->saveMany($checklist);
        }

        //update the items
        foreach ($request->get('items') as $row) {
            $item = GoodsReceiptNoteItem::findOrFail($row['item_id']);
            $item->rejected_qty = $row['rejected_qty'];
            $item->update();
        }

        //update goods receipt note
        $stage = GoodsReceiptNoteUtils::stage()['INSPECTION_DONE'];
        $activity = new GoodsReceiptNoteActivity;
        $activity->goods_receipt_note_id = $request->get('goods_receipt_note_id');
        $activity->stage = $stage;
        $activity->outcome = GoodsReceiptNoteUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks'); //yes, it's a duplicate
        $activity->save();

        DB::commit();

        //todo notify approvers

        return ['data' => $inspectionNote];
    }

    /**
     * Display the specified resource.
     *
     * @param InspectionNote $inspectionNote
     * @return array
     */
    public function show(InspectionNote $inspectionNote): array
    {

        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'goodsReceiptNote',
            'goodsReceiptNote.purchaseOrder', 'goodsReceiptNote.latestActivity',
            'goodsReceiptNote.items']);

        $inspectionNote->load($meta->include);
        return ['data' => $inspectionNote];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInspectionNoteRequest $request
     * @param InspectionNote $inspectionNote
     * @return array
     */
    public function update(UpdateInspectionNoteRequest $request, InspectionNote $inspectionNote): array
    {
        DB::beginTransaction();
        $inspectionNote->goods_receipt_note_id = $request->get('goods_receipt_note_id');
        $inspectionNote->remarks = $request->get('remarks');
        $inspectionNote->update();

        //save checklist
        if ($request->has('checklist')) {
            //delete existing checklist
            $inspectionNote->checklist()->delete();

            $checklist = [];
            foreach ($request->checklist as $row) {
                $item = new InspectionChecklist;
                $item->feature = $row['feature'];
                $item->passed = $row['passed'];
                $checklist[] = $item;

            }
            $inspectionNote->checklist()->saveMany($checklist);
        }

        //update the items
        foreach ($request->get('items') as $row) {
            $item = GoodsReceiptNoteItem::findOrFail($row['item_id']);
            $item->rejected_qty = $row['rejected_qty'];
            $item->update();
        }

        return ['data' => $inspectionNote];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param InspectionNote $inspectionNote
     * @return Response
     */
    public function destroy(InspectionNote $inspectionNote)
    {
        $inspectionNote->delete();
        return \response()->noContent();
    }
}
