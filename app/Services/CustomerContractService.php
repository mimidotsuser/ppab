<?php

namespace App\Services;

use App\Contracts\ProductItemActivityContract;
use App\Models\CustomerContract;
use App\Models\EntryRemark;
use App\Models\ProductItemActivity;
use App\Utils\ProductItemActivityUtils;

class CustomerContractService
{

    public function createItemsContractActivities(array  $itemIds, CustomerContract $contract,
                                                  string $categoryCode, string $remarks)
    {
        $itemModels = ProductItemActivity::with(['productItem'])
            ->joinSub(function ($builder) use ($itemIds) {
                $builder->from(ProductItemActivity::query()->from)
                    ->selectRaw('MAX(id) as id')
                    ->groupBy(['product_item_id']);
            }, 'latest', 'latest.id', '=', ProductItemActivity::query()->from . '.id')
            ->whereIn('product_item_id', $itemIds)
            ->get();

        $remark = new EntryRemark;
        $remark->description = $remarks ?? 'N/A';
        $remark->save();

        $service = new ProductItemService();
        $activities = [];

        foreach ($itemModels as $model) {

            $activity = new ProductItemActivityContract;
            $activity->categoryCode = $categoryCode;
            $activity->categoryTitle = ProductItemActivityUtils::activityCategoryTitles()[$categoryCode];
            $activity->remark = $remark;
            $activity->eventModel = $contract;
            $activity->productItem = $model->productItem;
            $activity->customer = $model->location;
            $activity->covenant = $model->covenant;

            $activities[] = $service->serializeActivity($activity);

        }

        $contract->productItemEventActivities()->saveMany($activities);

    }


}
