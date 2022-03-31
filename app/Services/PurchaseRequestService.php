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
}
