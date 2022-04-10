<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerContractRequest;
use App\Http\Requests\UpdateCustomerContractRequest;
use App\Models\CustomerContract;
use App\Services\CustomerContractService;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerContractController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(CustomerContract::class, 'customer_contract');
    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $meta = $this->queryMeta(['created_at', 'category_code', 'start_date', 'expiry_date'],
            ['createdBy', 'productItems', 'customer']);

        return CustomerContract::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);

                    $query->orWhereBeginsWith('category_code', $searchTerm);
                    $query->orWhereLike('category_code', $searchTerm);

                    $query->orWhereBeginsWith('category_title', $searchTerm);
                    $query->orWhereLike('category_title', $searchTerm);
                });

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
     * @param StoreCustomerContractRequest $request
     * @return CustomerContract[]
     */
    public function store(StoreCustomerContractRequest $request, CustomerContractService $service)
    {

        DB::beginTransaction();
        $contract = new CustomerContract;
        $contract->customer_id = $request->get('customer_id');
        $contract->category_code = $request->get('category_code');
        $contract->category_title = $request->get('category_title');
        $contract->start_date = $request->get('start_date');
        $contract->expiry_date = $request->get('expiry_date');
        $contract->save();
        $contract->refresh();

        $itemIds = Arr::pluck($request->get('contract_items'), 'product_item_id');
        $contract->productItems()->syncWithPivotValues($itemIds, ['created_by_id' => Auth::id(),
            'updated_by_id' => Auth::id()]);

        $service->createItemsContractActivities($itemIds, $contract->id,
            ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_CREATED'], 'N/A');

        DB::commit();
        return ['data' => $contract];
    }

    /**
     * Display the specified resource.
     *
     * @param CustomerContract $customerContract
     * @return CustomerContract[]
     */
    public function show(CustomerContract $customerContract)
    {

        $meta = $this->queryMeta([], ['createdBy', 'productItems', 'customer']);

        $customerContract->load($meta->include);

        return ['data' => $customerContract];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerContractRequest $request
     * @param CustomerContract $customerContract
     * @return CustomerContract[]
     */
    public function update(UpdateCustomerContractRequest $request, CustomerContract $customerContract,
                           CustomerContractService       $service)
    {

        DB::beginTransaction();
        $customerContract->customer_id = $request->get('customer_id', $customerContract->customer_id);
        $customerContract->category_code = $request->get('category_code', $customerContract->category_code);
        $customerContract->category_title = $request->get('category_title', $customerContract->category_title);
        $customerContract->start_date = $request->get('start_date', $customerContract->start_date);
        $customerContract->expiry_date = $request->get('expiry_date', $customerContract->expiry_date);
        $customerContract->update();

        $itemIds = Arr::pluck($request->get('contract_items'), 'product_item_id');

        $changes = $customerContract->productItems()->syncWithPivotValues($itemIds,
            ['created_by_id' => Auth::id(), 'updated_by_id' => Auth::id()]);


        if (count($changes['detached']) > 0) {
            $service->createItemsContractActivities($changes['detached'], null,
                ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_UPDATED'], 'N/A');
        }

        if (count($changes['attached']) > 0) {
            $service->createItemsContractActivities($changes['attached'], $customerContract->id,
                ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_UPDATED'], 'N/A');
        }

        DB::commit();
        return ['data' => $customerContract];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CustomerContract $customerContract
     * @return Response
     */
    public function destroy(CustomerContract $customerContract)
    {
        $customerContract->delete();
        return \response()->noContent();
    }
}
