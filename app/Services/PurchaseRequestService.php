<?php

namespace App\Services;

use App\Events\B2BQtyModified;
use App\Models\StockBalance;

class PurchaseRequestService
{

    public function onFormCreate($items)
    {
        //increment respective b2b stock requests balance in pipeline
        foreach ($items as $row) {
            $model = StockBalance::where('product_id', $row['product_id'])->firstOrFail();
            B2BQtyModified::dispatch($model, $row['requested_qty']);
        }
    }

    public function OnVerificationFormQtyRejected(array $items)
    {
        foreach ($items as $model) {
            //deduct the B2B pipeline by rejected diff
            B2BQtyModified::dispatch($model->product->balance,
                ($model->verified_qty - $model->requested_qty));
        }
    }

    public function OnApprovalFormQtyRejected(array $items)
    {
        foreach ($items as $model) {
            //deduct the B2B pipeline by rejected diff
            B2BQtyModified::dispatch($model->product->balance,
                ($model->approved_qty - $model->verified_qty));
        }
    }
}
