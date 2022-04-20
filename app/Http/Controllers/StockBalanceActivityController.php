<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequisition;
use App\Models\StockBalanceActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StockBalanceActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $meta = $this->queryMeta(['out_of_stock'], ['createdBy', 'product', 'event', 'balance']);

        return StockBalanceActivity::with($meta->include)
            ->when($request->get('type'), function ($query, $type) {
                $morphKey = $type;

                if (strtolower($type) === 'material_request') {
                    $morphKey = key(Arr::where(Relation::morphMap(),
                        fn($key) => $key == MaterialRequisition::class));
                }

                $query->where('event_type', $morphKey);
            })
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

    /**
     * Display the specified resource.
     *
     * @param StockBalanceActivity $stockBalanceActivity
     * @return array
     */
    public function show(StockBalanceActivity $stockBalanceActivity): array
    {
        $meta = $this->queryMeta([], ['createdBy', 'product', 'event', 'balance']);
        $stockBalanceActivity->load($meta->include);

        return ['data' => $stockBalanceActivity];
    }
}
