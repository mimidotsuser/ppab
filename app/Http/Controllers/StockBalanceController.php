<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStockBalanceRequest;
use App\Models\StockBalance;
use App\Models\StockBalanceActivity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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

        Gate::allowIf(function ($user) {
            return $user->role->permissions->contains('name', 'purchaseRequests.create') ||
                $user->role->permissions->contains('name', 'stockBalances.viewAny');
        });

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
            ->paginate($meta->limit, '*', null, $meta->page);
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

        DB::beginTransaction();
        $qty_in_before = $stockBalance->qty_in;

        if ($request->get('total_qty_in')) {
            //update by adjustment
            $stockBalance->qty_in = $stockBalance->qty_in + ($request->get('total_qty_in') - $stockBalance->stock_balance);
        }

        $stockBalance->update();
        $stockBalance->refresh();

        $activity = new StockBalanceActivity;
        $activity->product_id = $stockBalance->product_id;
        $activity->stock_balance_id = $stockBalance->id;
        $activity->qty_in_before = $qty_in_before;
        $activity->qty_in_after = $stockBalance->qty_in;
        $activity->qty_out_before = $stockBalance->qty_out;
        $activity->qty_out_after = $stockBalance->qty_out;
        $activity->restock_qty_before = $stockBalance->b2b_qty_in_pipeline;
        $activity->restock_qty_after = $stockBalance->b2b_qty_in_pipeline;
        $activity->qty_pending_issue_before = $stockBalance->b2c_qty_in_pipeline;
        $activity->qty_pending_issue_after = $stockBalance->b2c_qty_in_pipeline;
        $activity->remarks = $request->get('remarks') ?? 'Stock balance adjustment';
        $activity->event()->associate($stockBalance);
        $activity->save();

        DB::commit();

        $stockBalance->load('product', 'updatedBy');
        return ['data' => $stockBalance];
    }

}
