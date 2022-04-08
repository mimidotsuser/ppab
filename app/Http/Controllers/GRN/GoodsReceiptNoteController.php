<?php

namespace App\Http\Controllers\GRN;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoodsReceiptNoteRequest;
use App\Http\Requests\UpdateGoodsReceiptNoteRequest;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteActivity;
use App\Models\GoodsReceiptNoteItem;
use App\Utils\GoodsReceiptNoteUtils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GoodsReceiptNoteController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GoodsReceiptNote::class, 'goods_receipt_note');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $meta = $this->queryMeta(['created_at', 'sn'],
            ['createdBy', 'updatedBy', 'items', 'latestActivity', 'purchaseOrder']);

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
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreGoodsReceiptNoteRequest $request
     * @return array
     */
    public function store(StoreGoodsReceiptNoteRequest $request): array
    {

        DB::beginTransaction();
        $note = new GoodsReceiptNote;
        $note->purchase_order_id = $request->get('purchase_order_id');
        $note->reference = $request->get('reference');
        $note->warehouse_id = $request->get('warehouse_id');
        $note->save();
        $note->refresh();

        $items = [];
        foreach ($request->get('items') as $row) {
            $item = new GoodsReceiptNoteItem;
            $item->product_id = $row['product_id'];
            $item->po_item_id = $row['po_item_id'];
            $item->delivered_qty = $row['delivered_qty'];
            $item->request()->associate($note);
            $items[] = $item;
        }
        $note->items()->saveMany($items);

        $stage = GoodsReceiptNoteUtils::stage()['REQUEST_CREATED'];

        $activity = new GoodsReceiptNoteActivity;
        $activity->stage = $stage;
        $activity->outcome = GoodsReceiptNoteUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks', 'N/A');
        $activity->request()->associate($note);
        $activity->save();

        DB::commit();

        return ['data' => $note];
    }

    /**
     * Display the specified resource.
     *
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return GoodsReceiptNote[]
     */
    public function show(GoodsReceiptNote $goodsReceiptNote): array
    {
        $meta = $this->queryMeta([],
            ['createdBy', 'updatedBy', 'items', 'latestActivity', 'purchaseOrder']);
        $goodsReceiptNote->load($meta->include);
        return ['data' => $goodsReceiptNote];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateGoodsReceiptNoteRequest $request
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return GoodsReceiptNote[]
     */
    public function update(UpdateGoodsReceiptNoteRequest $request, GoodsReceiptNote $goodsReceiptNote)
    {
        $goodsReceiptNote->purchase_order_id = $request->get('purchase_order_id');
        $goodsReceiptNote->reference = $request->get('reference');
        $goodsReceiptNote->warehouse_id = $request->get('warehouse_id');
        $goodsReceiptNote->reference = $request->get('reference');
        $goodsReceiptNote->update();
        $goodsReceiptNote->refresh();

        return ['data' => $goodsReceiptNote];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return Response
     */
    public function destroy(GoodsReceiptNote $goodsReceiptNote)
    {
        $goodsReceiptNote->delete();
        return \response()->noContent();
    }
}
