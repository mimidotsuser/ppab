<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JetBrains\PhpStorm\ArrayShape;

class CustomerController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'name', 'region', 'branch'],
            ['createdBy', 'parent'], 'name');

        return Customer::search($request->search)
            ->query(function ($query) use ($meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->query(fn(Builder $query) => $query->with($meta->include))
            ->paginate($meta->limit, 'page', $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerRequest $request
     * @return array
     */
    #[ArrayShape(['data' => "\App\Models\Customer"])]
    public function store(StoreCustomerRequest $request): array
    {
        $customer = new Customer;
        $customer->parent_id = $request->get('parent_id');
        $customer->name = $request->get('name');
        $customer->branch = $request->get('branch');
        $customer->region = $request->get('region');
        $customer->location = $request->get('location');

        $customer->save();

        $customer->refresh();
        $customer->load(['createdBy', 'parent']);
        return ['data' => $customer];
    }

    /**
     * Display the specified resource.
     *
     * @param Customer $customer
     * @return array
     */
    #[ArrayShape(['data' => "\App\Models\Customer"])]
    public function show(Customer $customer): array
    {
        $customer->load('createdBy');
        return ['data' => $customer];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerRequest $request
     * @param Customer $customer
     * @return array
     */
    #[ArrayShape(['data' => "\App\Models\Customer"])]
    public function update(UpdateCustomerRequest $request, Customer $customer): array
    {
        $customer->parent_id = $request->get('parent_id') ?? $customer->parent_id;
        $customer->name = $request->get('name') ?? $customer->name;
        $customer->branch = $request->get('branch') ?? $customer->branch;
        $customer->region = $request->get('region') ?? $customer->region;
        $customer->location = $request->get('location') ?? $customer->location;

        $customer->save();

        $customer->refresh();
        $customer->load(['createdBy', 'parent']);
        return ['data' => $customer];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Customer $customer
     * @return Response
     */
    public function destroy(Customer $customer): Response
    {
        $customer->delete();

        return response()->noContent();
    }
}
