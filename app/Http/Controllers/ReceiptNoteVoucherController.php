<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceiptNoteVoucherRequest;
use App\Http\Requests\UpdateReceiptNoteVoucherRequest;
use App\Models\ReceiptNoteVoucher;
use App\Models\ReceiptNoteVoucherActivity;
use App\Models\ReceiptNoteVoucherItem;
use App\Utils\ReceiptNoteVoucherUtils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReceiptNoteVoucherController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ReceiptNoteVoucher::class, 'receipt_note_voucher');
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

        return ReceiptNoteVoucher::with($meta->include)
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
     * @param StoreReceiptNoteVoucherRequest $request
     * @return array
     */
    public function store(StoreReceiptNoteVoucherRequest $request): array
    {

        DB::beginTransaction();
        $note = new ReceiptNoteVoucher;
        $note->purchase_order_id = $request->get('purchase_order_id');
        $note->reference = $request->get('reference');
        $note->warehouse_id = $request->get('warehouse_id');
        $note->save();
        $note->refresh();

        $items = [];
        foreach ($request->get('items') as $row) {
            $item = new ReceiptNoteVoucherItem;
            $item->product_id = $row['product_id'];
            $item->po_item_id = $row['po_item_id'];
            $item->delivered_qty = $row['delivered_qty'];
            $item->request()->associate($note);
            $items[] = $item;
        }
        $note->items()->saveMany($items);

        $stage = ReceiptNoteVoucherUtils::stage()['REQUEST_CREATED'];

        $activity = new ReceiptNoteVoucherActivity;
        $activity->stage = $stage;
        $activity->outcome = ReceiptNoteVoucherUtils::outcome()[$stage];
        $activity->remarks = $request->get('remarks', 'N/A');
        $activity->request()->associate($note);
        $activity->save();

        DB::commit();

        return ['data' => $note];
    }

    /**
     * Display the specified resource.
     *
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return ReceiptNoteVoucher[]
     */
    public function show(ReceiptNoteVoucher $receiptNoteVoucher): array
    {
        $meta = $this->queryMeta([],
            ['createdBy', 'updatedBy', 'items', 'latestActivity', 'purchaseOrder']);
        $receiptNoteVoucher->load($meta->include);
        return ['data' => $receiptNoteVoucher];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateReceiptNoteVoucherRequest $request
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return ReceiptNoteVoucher[]
     */
    public function update(UpdateReceiptNoteVoucherRequest $request, ReceiptNoteVoucher $receiptNoteVoucher)
    {
        $receiptNoteVoucher->purchase_order_id = $request->get('purchase_order_id');
        $receiptNoteVoucher->reference = $request->get('reference');
        $receiptNoteVoucher->warehouse_id = $request->get('warehouse_id');
        $receiptNoteVoucher->reference = $request->get('reference');
        $receiptNoteVoucher->update();
        $receiptNoteVoucher->refresh();

        return ['data' => $receiptNoteVoucher];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return Response
     */
    public function destroy(ReceiptNoteVoucher $receiptNoteVoucher)
    {
        $receiptNoteVoucher->delete();
        return \response()->noContent();
    }
}
