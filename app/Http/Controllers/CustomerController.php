<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JetBrains\PhpStorm\ArrayShape;

class CustomerController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Customer::class, 'customer', [
            'except' => ['index']
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        if ($request->search) {
            $this->authorize('search', Customer::class);
        } else {
            $this->authorize('viewAny', Customer::class);
        }

        $meta = $this->queryMeta(['created_at', 'name', 'region', 'branch'],
            ['createdBy', 'parent'], 'name');

        return Customer::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('name', $searchTerm);
                    $query->orWhereLike('name', $searchTerm);

                    $query->orWhereBeginsWith('region', $searchTerm);
                    $query->orWhereLike('region', $searchTerm);
                    $query->orWhereBeginsWith('branch', $searchTerm);
                    $query->orWhereLike('branch', $searchTerm);
                });

            })
            ->when($request->boolean('parentsOnly', false), function (Builder $query) {
                return $query->whereNull('parent_id');

            })
            ->when($request->boolean('childrenOnly', false), function (Builder $query) {
                return $query->whereNotNull('parent_id');
            })
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, '*', null, $meta->page);
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
