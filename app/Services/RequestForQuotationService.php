<?php

namespace App\Services;

use App\Events\B2BQtyModified;
use App\Models\StockBalance;

class RequestForQuotationService
{

    public function updateProductB2BBalance(array $items)
    {
        //increment respective b2b stock requests balance in pipeline
        foreach ($items as $row) {
            if ($row['by'] === 0) {
                continue;
            }
            $model = StockBalance::where('product_id', $row['id'])->firstOrFail();
            B2BQtyModified::dispatch($model, $row['by']);
        }
    }
}
