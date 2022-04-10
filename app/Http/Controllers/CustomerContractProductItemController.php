<?php

namespace App\Http\Controllers;

use App\Models\CustomerContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CustomerContractProductItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request, CustomerContract $customerContract)
    {
        $meta = $this->queryMeta([], []);
        return $customerContract
            ->contractItems()
            ->when($request->get('ids'), function ($query, $productIds) {
                $query->whereIn('product_item_id', explode(',', $productIds));
            })
            ->paginate($meta->limit, '*', null, $meta->page);
    }
}
