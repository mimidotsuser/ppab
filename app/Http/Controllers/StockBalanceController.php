<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStockBalanceRequest;
use App\Models\StockBalance;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class StockBalanceController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     * @throws AuthorizationException
     */
    public function index(Request $request): LengthAwarePaginator
    {

        $this->authorize('viewAny', StockBalance::class);
        $meta = $this->queryMeta(['out_of_stock'],
            ['createdBy', 'updatedBy', 'product', 'warehouse']);

        return StockBalance::with($meta->include)
            ->when($request->boolean('exclude_variants', false), function ($query) {
                $query->whereRelation('product', 'variant_of_id', null);
            })->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, '*', 'page', $meta->page);
    }


    /**
     * Display the specified resource.
     *
     * @param StockBalance $stockBalance
     * @return StockBalance[]
     */
    #[ArrayShape(['data' => "\App\Models\StockBalance"])]
    public function show(StockBalance $stockBalance): array
    {
        $this->authorize('view', $stockBalance);

        $stockBalance->load('product', 'updatedBy', 'warehouse');
        return ['data' => $stockBalance];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStockBalanceRequest $request
     * @param StockBalance $stockBalance
     * @return StockBalance[]
     * @throws AuthorizationException
     */
    #[ArrayShape(['data' => "\App\Models\StockBalance"])]
    public function update(UpdateStockBalanceRequest $request, StockBalance $stockBalance): array
    {
        $this->authorize('update', $stockBalance);

        $stockBalance->qty_in = $request->get('total_qty_in') ?? $stockBalance->qty_in;
        $stockBalance->qty_out = $request->get('total_qty_out') ?? $stockBalance->qty_out;
        $stockBalance->b2c_qty_in_pipeline = $request->get('issue_requests_total') ??
            $stockBalance->b2c_qty_in_pipeline;
        $stockBalance->b2b_qty_in_pipeline = $request->get('reorder_requests_total') ??
            $stockBalance->b2b_qty_in_pipeline;
        $stockBalance->update();
        $stockBalance->refresh();
        $stockBalance->load('product', 'updatedBy');
        return ['data' => $stockBalance];
    }

}
