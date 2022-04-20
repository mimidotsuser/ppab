<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStandbySpareCheckinRequest;
use App\Models\MaterialRequisition;
use App\Models\StockBalance;
use App\Models\StockBalanceActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StandbySpareCheckinController extends Controller
{
    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        Gate::allowIf(fn($user) => $user->role->permissions->contains('name', 'standByCheckIn.view'));

        $meta = $this->queryMeta(['out_of_stock'], ['createdBy', 'product', 'event', 'balance']);
        $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == MaterialRequisition::class));

        return StockBalanceActivity::with($meta->include)
            ->where('event_type', $morphKey)
            ->when($request->get('material_request_id'), function ($query, $mrnId) {
                $query->where_in('event_id', $mrnId);
            })
            ->when($request->get('product_ids'), function ($query, $productIds) {
                $query->where_in('product_id', explode(',', $productIds));
            })
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, '*', null, $meta->page);

    }


    public function store(StoreStandbySpareCheckinRequest $request)
    {
        DB::beginTransaction();

        $materialRequisition = MaterialRequisition::find($request->get('material_request_id'));

        $productIds = Arr::pluck($request->get('items'), 'product_id');

        $balances = StockBalance::whereIn('product_id', $productIds)->get();
        $activities = [];
        foreach ($balances as $balance) {

            $item = Arr::first($request->get('items'),
                fn($row) => $row['product_id'] == $balance->product_id);

            //create and populate a stock balance activity model instance
            $activity = new StockBalanceActivity;
            $activity->product_id = $balance->product_id;
            $activity->stock_balance_id = $balance->id;
            $activity->qty_in_before = $balance->qty_in;
            $activity->qty_in_after = $balance->qty_in + $item['qty'];
            $activity->qty_out_before = $balance->qty_out;
            $activity->qty_out_after = $balance->qty_out;
            $activity->restock_qty_before = $balance->b2b_qty_in_pipeline;
            $activity->restock_qty_after = $balance->b2b_qty_in_pipeline;
            $activity->qty_pending_issue_before = $balance->b2c_qty_in_pipeline;
            $activity->qty_pending_issue_after = $balance->b2c_qty_in_pipeline;
            $activity->remarks = $request->get('remarks');
            $activity->event()->associate($materialRequisition);
            $activity->save();

            $activities[] = $activity;
            //update stock balance model
            $balance->qty_in += $item['qty'];
            $balance->update();
        }


        DB::commit();

        return ['data' => $activities];
    }
}
