<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProductItem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class CustomerProductItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Customer $customer, Request $request)
    {

        $meta = $this->queryMeta(['created_at', 'product_id', 'sn', 'serial_number'],
            ['createdBy', 'updatedBy', 'product', 'latestActivity', 'latestActivity.location',
                'latestActivity.warrant', 'activities', 'activities.location', 'activities.warrant',
                'activities.createdBy', 'activities.remark', 'activities.repair',
                'activeWarrant', 'activeWarrants', 'oldestActivity', 'latestActiveContracts']);

        $lastContractIndex = array_search('latestActiveContracts', $meta->include);
        if ($lastContractIndex !== false) {

            array_splice($meta->include, $lastContractIndex, 1);
            $meta->include["latestContracts"] = function ($query) {
                $query->where('active', true)
                    ->whereDate('expiry_date', '>', Carbon::yesterday());
            };
        }

        return ProductItem::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereLike('sn', $searchTerm);
                    $query->orWhereLike('serial_number', $searchTerm);
                });
            })
            ->whereHas('latestActivity', function ($query) use ($customer) {

                $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Customer::class));
                $query->where('location_type', $morphKey);

                //filter by customer  + children if
                if (\request()->boolean('includeChildrenItems', false)) {
                    $query->whereIn('location_id', $customer->children->pluck('id')
                        ->merge($customer->id));
                } else {
                    $query->whereIn('location_id', [$customer->id]);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($meta->limit, '*', null, $meta->page);

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
