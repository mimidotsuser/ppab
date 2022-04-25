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
            ->when($request->filled('active'), function ($query) {
                $query->where('active', \request()->boolean('active'));
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

        $service->createItemsContractActivities($itemIds, $contract,
            ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_CREATED']);

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

        $meta = $this->queryMeta([], ['createdBy', 'productItems', 'productItems.product', 'customer']);

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

        $newContractIds = Arr::pluck($request->get('contract_items', []), 'product_item_id');
        $oldContractIds = Arr::pluck($customerContract->contractItems, 'product_item_id');

        //find the items that have been removed
        $detached = array_diff($oldContractIds, $newContractIds);
        // find the items that have been added
        $attached = array_diff($newContractIds, $oldContractIds);

        //set current contract as in-active
        $customerContract->active = false;
        $customerContract->update();

        //if the items were removed from current contract,
        if (count($newContractIds) === 0) {

            //update items activity
            if (count($detached) > 0) {
                $service->createItemsContractActivities($detached, $customerContract,
                    ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_UPDATED'],);
            }

            DB::commit();
            //stop further logic execution
            return ['data' => $customerContract];
        }

        //save the contract as new
        $newContract = new CustomerContract;
        $newContract->previous_version_id = $customerContract->id;

        $newContract->customer_id = $request->get('customer_id', $customerContract->customer_id);
        $newContract->category_code = $request->get('category_code', $customerContract->category_code);
        $newContract->category_title = $request->get('category_title', $customerContract->category_title);
        $newContract->start_date = $request->get('start_date', $customerContract->start_date);
        $newContract->expiry_date = $request->get('expiry_date', $customerContract->expiry_date);
        $newContract->save();
        $newContract->refresh();


        $newContract->productItems()->syncWithPivotValues($newContractIds,
            ['created_by_id' => Auth::id(), 'updated_by_id' => Auth::id()]);


        if (count($detached) > 0) {
            $service->createItemsContractActivities($detached, $newContract,
                ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_UPDATED'],);
        }

        if (count($attached) > 0) {
            $service->createItemsContractActivities($attached, $newContract,
                ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_CREATED']);
        }

        DB::commit();
        return ['data' => $newContract];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CustomerContract $customerContract
     * @return Response
     */
    public function destroy(CustomerContract $customerContract, CustomerContractService $service)
    {
        $service->createItemsContractActivities(
            Arr::pluck($customerContract->contractItems, 'product_item_id'),
            $customerContract,
            ProductItemActivityUtils::activityCategoryCodes()['CONTRACT_DELETED'], 'N/A');

        $customerContract->delete();
        return \response()->noContent();
    }
}
