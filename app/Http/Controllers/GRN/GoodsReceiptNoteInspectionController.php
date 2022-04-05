<?php

namespace App\Http\Controllers\GRN;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Utils\GoodsReceiptNoteUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GoodsReceiptNoteInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {

        Gate::allowIf(fn($user) => $user->role->permissions->contains('name', 'inspectionReport.view'));

        $meta = $this->queryMeta(['created_at', 'sn'],
            ['createdBy', 'updatedBy', 'items', 'latestActivity', 'purchaseOrder',
                'purchaseOrder.vendor', 'activities']);

        $stage = GoodsReceiptNoteUtils::stage()['REQUEST_CREATED'];

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
            ->paginate($meta->limit, '*', 'page', $meta->page);
    }


    /**
     * Display the specified resource.
     *
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return array|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(GoodsReceiptNote $goodsReceiptNote)
    {

        Gate::allowIf(fn($user) => $user->role->permissions->contains('name', 'inspectionReport.view'));

        $stage = GoodsReceiptNoteUtils::stage()['REQUEST_CREATED'];

        if ($goodsReceiptNote->latestActivity->stage != $stage) {
            return \response()->noContent(404);
        }

        $meta = $this->queryMeta([], ['createdBy', 'updatedBy', 'items', 'activities',
            'purchaseOrder','items.product', 'purchaseOrder.vendor']);

        $goodsReceiptNote->load($meta->include);
        return ['data' => $goodsReceiptNote];
    }

}
