<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorksheetRequest;
use App\Http\Requests\UpdateWorksheetRequest;
use App\Models\Customer;
use App\Models\EntryRemark;
use App\Models\ProductItem;
use App\Models\ProductRepair;
use App\Models\ProductRepairItem;
use App\Models\ProductTrackingLog;
use App\Models\Worksheet;
use App\Utils\WorksheetUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $meta = $this->queryMeta(['created_at', 'product_id', 'sn', 'serial_number'],
            ['createdBy', 'customer', 'entries','entries.createdBy', 'entries.productItem', 'entries.warrant',
                'entries.repair', 'entries.repair.sparesUtilized',
                'entries.repair.sparesUtilized.product']);

        return Worksheet::with($meta->include)
            ->when($request->search, function ($query) use ($request) {
                $query->whereLike('sn', $request->search);
                $query->orWhereLike('reference', $request->search);
            })
            ->when($request->get('total'), function ($query) {
                $query->withCount('entries');
            })
            ->paginate($meta->limit, '*', 'page',$meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWorksheetRequest $request
     * @return Worksheet[]
     */
    public function store(StoreWorksheetRequest $request)
    {
        DB::beginTransaction();

        $worksheet = new Worksheet;
        $worksheet->customer_id = $request->get('customer_id');
        $worksheet->reference = $request->get('reference');
        $worksheet->save();

        foreach ($request->get('entries') as $entry) {
            //save the work description
            $remark = new EntryRemark;
            $remark->description = $entry['description'];
            $remark->save();

            $repair = null;
            //save any repair logs if available
            if (!empty($entry['repair_items'])) {
                $repair = new ProductRepair;
                $repair->save();
                $repairParts = [];
                foreach ($entry['repair_items'] as $item) {
                    $partModel = new  ProductRepairItem($item);
                    $partModel->created_by_id = Auth::id();
                    $partModel->updated_by_id = Auth::id();
                    $repairParts[] = $partModel;
                }

                $repair->sparesUtilized()->saveMany($repairParts);
            }


            //create entry logs for each product item
            $categoryCode = $entry['category_code'];
            $categoryTitle = WorksheetUtils::getWorksheetCategories()[$categoryCode];

            foreach ($entry['product_items'] as $item) {
                $productItem = ProductItem::findOrFail($item['id']);

                $trackingEntry = new ProductTrackingLog;
                $trackingEntry->log_category_code = $categoryCode;
                $trackingEntry->log_category_title = $categoryTitle;
                $trackingEntry->productItem()->associate($productItem);
                $trackingEntry->location()
                    ->associate(Customer::find($request->get('customer_id')));
                $trackingEntry->remark()->associate($remark);

                //carry over warrant if not expired
                $warrant = $productItem->lastWarrant;
                if ($warrant && Carbon::parse($warrant->warrant_end)->addDay()->isFuture()) {
                    $trackingEntry->product_warrant_id = $warrant->id;
                }

                //carry over contract if not expired
                $contract = $productItem->lastContract;
                if ($contract && Carbon::parse($contract->expiry_date)->addDay()->isFuture()) {
                    $trackingEntry->customer_contract_id = $contract->id;
                }

                $trackingEntry->eventable()->associate($worksheet);

                $trackingEntry->repair()->associate($repair);

                $trackingEntry->save();
            }
        }
        DB::commit();

        $worksheet->load(['customer', 'entries', 'createdBy', 'entries', 'entries.repair.sparesUtilized']);

        return ['data' => $worksheet];
    }

    /**
     * Display the specified resource.
     *
     * @param Worksheet $worksheet
     * @return Worksheet[]
     */
    public function show(Worksheet $worksheet)
    {
        $meta = $this->queryMeta([],
            ['createdBy', 'customer', 'entries', 'entries.productItem', 'entries.warrant',
                'entries.repair', 'entries.repair.sparesUtilized',
                'entries.repair.sparesUtilized.product']);

        $worksheet->load($meta->included);
        return ['data' => $worksheet];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateWorksheetRequest $request
     * @param Worksheet $worksheet
     * @return Worksheet[]
     */
    public function update(UpdateWorksheetRequest $request, Worksheet $worksheet)
    {
        //TODO


        $worksheet->refresh();
        return ['data' => $worksheet];

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Worksheet $worksheet
     * @return Response
     */
    public function destroy(Worksheet $worksheet)
    {
        $worksheet->delete();
        return response()->noContent();
    }
}
