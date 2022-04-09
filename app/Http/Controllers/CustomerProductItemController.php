<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerProductItemCollection;
use App\Models\Customer;
use App\Models\ProductItem;
use App\Models\ProductItemActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerProductItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return CustomerProductItemCollection
     */
    public function index(Customer $customer, Request $request)
    {

        $meta = $this->queryMeta();

        $items = ProductItemActivity::with(['productItem.product'])
            ->select(['id', 'product_item_id', 'location_id', 'location_type'])
            ->whereExists(function ($builder) {
                $builder->selectRaw('product_item_id,MAX(created_at)')
                    ->groupBy('product_item_id');
            })
            ->whereHasMorph('location', Customer::class, function ($query) use ($customer) {
                if (\request()->boolean('includeChildrenItems', false)) {
                    $query->whereIn('id', $customer->children->pluck('id')
                        ->merge($customer->id));
                } else {
                    $query->whereIn('id', $customer);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($meta->limit, '*', null, $meta->page);

        return CustomerProductItemCollection::make($items);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param ProductItem $productItem
     * @return Response
     */
    public function show(ProductItem $productItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ProductItem $productItem
     * @return Response
     */
    public function update(Request $request, ProductItem $productItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ProductItem $productItem
     * @return Response
     */
    public function destroy(ProductItem $productItem)
    {
        //
    }
}
