<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProductFilterController extends Controller
{

    /**
     * @param Request $request
     * @param ProductCategory $productCategory
     * @return LengthAwarePaginator
     */
    public function index(Request $request, ProductCategory $productCategory): LengthAwarePaginator
    {

        $meta = $this->queryMeta(['created_at', 'item_code', 'economic_order_qty', 'min_level',
            'reorder_level', 'max_level'], ['createdBy', 'updatedBy', 'parent', 'balance',
            'aggregateBalance']);

        //if not searching but includes parent key
        $withParentOnly = empty($request->search) && !empty($request->query('parent_id'));


        return $productCategory
            ->products()
            ->when(!empty($meta->include), function ($query) use ($meta) {
                $query->with($meta->include);
            })
            ->when($withParentOnly, function ($query) use ($request) {
                $query->where('parent_id', $request->query('parent_id'));
            })
            ->when(!$request->get('variants'), function ($query) {
                $query->whereNull('variant_of_id');
            })
            ->when(!empty($request->search), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orWhereLike('item_code', $request->search, false);
                    $query->orWhereLike('item_code', $request->search);
                    $query->orWhereLike('description', $request->search, false);
                    $query->orWhereLike('description', $request->search);
                    $query->orWhereLike('local_description', $request->search, false);
                    $query->orWhereLike('local_description', $request->search);

                    if ($request->query('parent_id', false)) {
                        $query->whereHas('parent_id', function ($query) use ($request) {
                            $query->orWhereLike('item_code', $request->search);
                            $query->orWhereLike('description', $request->search);
                        });

                    }
                });

            })
            ->paginate($meta->limit, '*', 'page', $meta->page);

    }
}
