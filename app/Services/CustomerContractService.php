<?php

namespace App\Services;

use App\Models\EntryRemark;
use App\Models\ProductItemActivity;
use App\Utils\ProductItemActivityUtils;

class CustomerContractService
{

    public function createItemsContractActivities(array  $itemIds, int $contractId,
                                                  string $categoryCode, string $remarks)
    {
        $itemModels = ProductItemActivity::with(['productItem.product'])
            ->whereExists(function ($builder) {
                $builder->selectRaw('product_item_id,MAX(created_at)')
                    ->groupBy('product_item_id');
            })->whereIn('product_item_id', $itemIds)
            ->get();

        $remark = new EntryRemark;
        $remark->description = $remarks ?? 'N/A';
        $remark->save();


        foreach ($itemModels as $model) {
            $activity = $model->replicate([]);
            $activity->remark()->associate($remark);
            $activity->log_category_code = $categoryCode;
            $activity->log_category_title = ProductItemActivityUtils::activityCategoryTitles()[$categoryCode];

            if (isset($contractId)) {
                $activity->customer_contract_id = $contractId;
            }
            $activity->warrant()->associate($activity->productItem->activeWarrant);

            $activity->location()->associate($activity->location);

            $activity->save();
        }


    }


}
