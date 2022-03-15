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
        $sparesOnly = empty($request->search) && !empty($request->query('parent'));


        return $productCategory
            ->products()
            ->when(!empty($meta->include), function ($query) use ($meta) {
                $query->with($meta->include);
            })
            ->when($sparesOnly, function ($query) use ($request) {
                $query->where('parent_id', $request->query('parent'));
            })
            ->when(!$request->get('variants'), function ($query) {
                $query->whereNull('variant_of');
            })
            ->when(!empty($request->search), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->orWhereFullText('item_code', $request->search);
                    $query->orWhereFullText('description', $request->search);
                    $query->orWhereFullText('local_description', $request->search);

                    if ($request->query('parent', false)) {
                        $query->whereHas('parent', function ($query) use ($request) {
                            $query->orWhereFullText('item_code', $request->search);
                            $query->orWhereFullText('description', $request->search);
                        });

                    }
                });

            })
            ->paginate($meta->limit, '*', $meta->page);

    }
}
