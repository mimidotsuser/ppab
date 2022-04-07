<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorksheetRequest;
use App\Models\Customer;
use App\Models\EntryRemark;
use App\Models\ProductItem;
use App\Models\ProductItemActivity;
use App\Models\ProductItemRepair;
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
        $meta = $this->queryMeta(['created_at', 'sn', 'reference', 'customer_id'],
            ['createdBy', 'updatedBy', 'customer', 'entries', 'entries.location',
                'entries.warrant', 'entries.location', 'entries.warrant',
                'entries.createdBy', 'entries.remark', 'entries.repair', 'entries.repair.products',
                'entries.repair.sparesUtilized'
            ]);

        return Worksheet::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('sn', $searchTerm);
                    $query->orWhereLike('sn', $searchTerm);

                    $query->orWhereBeginsWith('reference', $searchTerm);
                    $query->orWhereLike('reference', $searchTerm);
                });

            })
            ->when($request->get('total'), function ($query) {
                $query->withCount('entries');
            })
            ->paginate($meta->limit, '*', 'page', $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWorksheetRequest $request
     * @return array
     */
    public function store(StoreWorksheetRequest $request): array
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
                $repair = new ProductItemRepair;
                $repair->save();


                $repairParts = array_reduce($entry['repair_items'], function ($acc, $row) {
                    $acc[$row['product_id']] = [
                        'old_total' => $row['old_total'],
                        'new_total' => $row['new_total'],
                        'created_by_id' => Auth::id(),
                        'updated_by_id' => Auth::id(),
                    ];
                    return $acc;
                }, []);

                $repair->products()->sync($repairParts);
            }


            //create entry logs for each product item
            $categoryCode = $entry['category_code'];
            $categoryTitle = WorksheetUtils::worksheetCategoryTitles()[$categoryCode];

            if (isset($entry['product_items'])) {
                $activities = [];
                foreach ($entry['product_items'] as $item) {
                    $productItem = ProductItem::findOrFail($item['id']);

                    $activity = new ProductItemActivity;
                    $activity->log_category_code = $categoryCode;
                    $activity->log_category_title = $categoryTitle;
                    $activity->remark()->associate($remark);
                    $activity->productItem()->associate($productItem);
                    $activity->location()
                        ->associate(Customer::find($request->get('customer_id')));

                    //carry over covenant
                    $activity->covenant = $productItem->latestActivity->covenant;

                    //carry over warrant if not expired
                    $warrant = $productItem->activeWarrant;
                    if (isset($warrant)) {
                        $activity->warrant()->associate($warrant);
                    }

                    //carry over contract if is active
                    $contract = $productItem->lastContract;
                    if (!$contract->isEmpty()) {
                        if (Carbon::parse($contract->start_date)->addDay()->isPast()
                            && Carbon::parse($contract->expiry_date)->addDay()->isFuture()) {
                            $activity->contract()->associate($contract);
                        }
                    }

                    $activity->eventable()->associate($worksheet);

                    $activity->repair()->associate($repair);

                    $activities[] = $activity;
                }

                $worksheet->entries()->saveMany($activities);
            }
        }
        DB::commit();

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
        $meta = $this->queryMeta(['created_at', 'sn', 'reference', 'customer_id'],
            ['createdBy', 'updatedBy', 'customer', 'entries', 'entries.location',
                'entries.warrant', 'entries.location', 'entries.warrant',
                'entries.createdBy', 'entries.remark', 'entries.repair'
            ]);

        $worksheet->load($meta->include);

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
