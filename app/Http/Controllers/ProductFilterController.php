<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

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


        return $productCategory
            ->products()
            ->when(!empty($meta->include), function ($query) use ($meta) {
                $query->with($meta->include);
            })
            ->when(!empty($request->search), function ($query) use ($request) {
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
                });

            })
            ->paginate($meta->limit, '*', 'page', $meta->page);

    }

    /**
     * Fetch product balances and that of closely related models (variants)
     * @param Request $request
     * @param Product $product
     * @return array
     */
    #[ArrayShape(['data' => "\Illuminate\Database\Eloquent\Collection"])]
    public function productBalances(Request $request, Product $product): array
    {
        $data = $product->meldedBalances()
            ->with(['product:id,item_code,variant_of_id,parent_id,product_category_id'])
            ->get();
        return ['data' => $data];
    }
}
