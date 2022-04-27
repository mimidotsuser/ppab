<?php

namespace App\Http\Controllers;

use App\Contracts\ProductItemActivityContract;
use App\Http\Requests\StoreWorksheetRequest;
use App\Models\Customer;
use App\Models\EntryRemark;
use App\Models\ProductItem;
use App\Models\ProductItemRepair;
use App\Models\Worksheet;
use App\Services\ProductItemService;
use App\Utils\WorksheetUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
                'entries.warrant', 'entries.contract', 'entries.createdBy', 'entries.remark',
                'entries.productItem.product', 'entries.repair', 'entries.repair.products',
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
            ->when($request->get('start_date'), function (Builder $builder, $startDate) {
                $builder->whereDate('created_at', '>', $startDate);
            })
            ->when($request->get('end_date'), function (Builder $builder, $endDate) {
                $builder->whereDate('created_at', '<', $endDate);
            })
            ->when($request->get('customers'), function (Builder $builder, $customerIds) {
                $builder->whereIn('customer_id', explode(',', $customerIds));
            })
            ->when($request->get('created_by'), function (Builder $builder, $authorIds) {
                $builder->whereIn('created_by_id', explode(',', $authorIds));
            })
            ->when($request->get('entry_categories'), function (Builder $builder, $entryCat) {

                $builder->whereHas('entries', function ($query) use ($entryCat) {
                    $query->where(function ($query) use ($entryCat) {
                        $entryCategories = explode(',', $entryCat);
                        foreach ($entryCategories as $category) {
                            $query->orWhere('log_category_code', $category);
                        }
                    });
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
     * @param StoreWorksheetRequest $request
     * @return array
     */
    public function store(StoreWorksheetRequest $request, ProductItemService $productItemService): array
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

                    $activity = new ProductItemActivityContract;
                    $activity->categoryCode = $categoryCode;
                    $activity->categoryTitle = $categoryTitle;
                    $activity->remark = $remark;
                    $activity->productItem = $productItem;
                    $activity->eventModel = $worksheet;
                    $activity->customer = Customer::find($request->get('customer_id'));
                    $activity->covenant = $productItem->latestActivity->covenant;;
                    if (isset($repair)) {
                        $activity->repairModel = $repair;
                    }

                    $activities[] = $productItemService->serializeActivity($activity);
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
                'entries.productItem.product', 'entries.warrant', 'entries.contract',
                'entries.createdBy', 'entries.remark', 'entries.repair', 'entries.repair.products',
                'entries.repair.sparesUtilized'
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
