<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class ProductCategoryFilterController extends Controller
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
            ->with($meta->include)
            ->when($request->get('parent_id'), function ($query,$parentId) {
                $query->where('parent_id', $parentId);
            })
            ->when(!$request->get('variants'), function ($query) {
                $query->whereNull('variant_of_id');
            })
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('item_code', $searchTerm);
                    $query->orWhereLike('item_code', $searchTerm,);
                    $query->orWhereBeginsWith('description', $searchTerm);
                    $query->orWhereLike('description', $searchTerm);
                    $query->orWhereBeginsWith('local_description', $searchTerm);
                    $query->orWhereLike('local_description', $searchTerm);
                });

            })->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, '*',  $meta->page);

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
