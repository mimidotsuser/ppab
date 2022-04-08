<?php

namespace App\Services;

use App\Events\B2CRequestQtyModified;
use App\Events\ProductCheckout;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class MaterialRequisitionService
{

    public function OnFormCreate($items)
    {
        //dispatch events to adjust the virtual stock balance accordingly
        foreach ($items as $row) {
            $model = Product::with('variants.balance', 'balance')
                ->findOrFail($row['product_id']);

            //if the model quantity requested can fit on the product
            if ($row['requested_qty'] <= $model->balance->virtual_balance) {

                B2CRequestQtyModified::dispatch($model, $row['requested_qty']);

            } else {

                if (empty($model->variants)) {
                    B2CRequestQtyModified::dispatch($model, $model->balance->virtual_balance);

                    Log::critical('MRF Request has excess items of ' . $row['product_id']
                        . ' by ' . $row['requested_qty']) - $model->balance->virtual_balance;

                } else {
                    // distribute the qty across the variants as much possible

                    //1) Book all parent Qty
                    B2CRequestQtyModified::dispatch($model, $model->balance->virtual_balance);

                    //2) remainder to be shared by variants
                    $remainderQty = $row['requested_qty'] - $model->balance->virtual_balance;
                    foreach ($model->variants as $variant) {
                        if ($remainderQty < $variant->balance->virtual_balance) {
                            B2CRequestQtyModified::dispatch($variant, $remainderQty);
                            $remainderQty = 0;
                            break;
                        } else {
                            $remainderQty -= $variant->balance->virtual_balance;
                            B2CRequestQtyModified::dispatch($variant, $variant->balance->virtual_balance);
                        }
                    }

                    if ($remainderQty > 0) {
                        //we have a huge problem, notify the administrator
                        Log::critical('MRF Request has excess items of ' . $row['product_id']
                            . ' by ' . $remainderQty);
                    }
                }
            }
        }

    }

    public function OnVerificationFormQtyRejected($items)
    {
        //dispatch events to adjust the virtual stock balance accordingly

        foreach ($items as $item) {
            //check if incrementing the virtual balance will exceed the stock_balance
            $decrementBy = $item->requested_qty - $item->verified_qty;
            $model = $item->product;
            $this->decrementB2CModelQty($model, $decrementBy);
        }
    }

    public function OnApprovalFormQtyRejected($items)
    {
        //dispatch events to adjust the virtual stock balance accordingly

        foreach ($items as $item) {
            //check if incrementing the virtual balance will exceed the stock_balance
            $decrementBy = $item->verified_qty - $item->approved_qty;
            $model = $item->product;
            $this->decrementB2CModelQty($model, $decrementBy);
        }
    }

    public function OnMachineIssue(Product $model, $qtyIssued)
    {
        // decrement the B2C in pipeline
        B2CRequestQtyModified::dispatch($model, $qtyIssued * -1);
        // increment Qty_out
        ProductCheckout::dispatch($model, $qtyIssued);
    }

    public function OnSpareIssue(Product $model, int $newQty, int $oldQty)
    {
        // decrement the B2C in pipeline
        $this->decrementB2CModelQty($model, $newQty + $oldQty);

        // increment Qty_out
        if ($newQty > 0) {
            ProductCheckout::dispatch($model, $newQty);
        }
        if ($oldQty > 0) {
            foreach ($model->variants as $variant) {
                //distribute it among all variants
                $remainder = $variant->balance->stock_balance - $oldQty;
                if ($remainder >= 0) {
                    ProductCheckout::dispatch($variant, $oldQty);
                } else {
                    ProductCheckout::dispatch($variant, $variant->balance->stock_balance);
                    $oldQty -= $variant->balance->stock_balance;
                }
            }
        }
    }

    private function decrementB2CModelQty(Product $model, int $by)
    {

        if ($by === 0) {
            return;
        }

        if ($model->balance->virtual_balance + $by <= $model->balance->stock_balance) {
            //if it's less or equal the balance,
            B2CRequestQtyModified::dispatch($model, $by * -1);
        } else {
            $tempDiff = ($model->balance->stock_balance - $model->balance->virtual_balance);
            $remainderQty = $by - $tempDiff;

            B2CRequestQtyModified::dispatch($model, $tempDiff * -1);

            //distribute the remainder Qty  to variants
            foreach ($model->variants as $variant) {
                if ($remainderQty + $variant->balance->virtual_balance <= $variant->balance->stock_balance) {
                    B2CRequestQtyModified::dispatch($variant, $remainderQty * -1);
                    $remainderQty = 0;
                    break;
                } else {
                    $tempDiff = ($model->balance->stock_balance - $model->balance->virtual_balance);
                    $remainderQty -= $tempDiff;
                    B2CRequestQtyModified::dispatch($variant, $tempDiff * -1);
                }
            }
            if ($remainderQty > 0) {
                //we have a huge problem, notify the administrator
                Log::critical('MRF Request cannot shelve back ' . $model->id
                    . ' by ' . $remainderQty);
            }
        }
    }
}
