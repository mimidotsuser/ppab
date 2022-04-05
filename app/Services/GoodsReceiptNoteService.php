<?php

namespace App\Services;

use App\Models\GoodsReceiptNote;

class GoodsReceiptNoteService
{
    public function syncB2BToQtyInBalance(GoodsReceiptNote $goodsReceiptNote)
    {
        foreach ($goodsReceiptNote->items as $item) {
            $balance = $item->product->balance;

            //decrement the B2B quantity
            $balance->b2b_qty_in_pipeline -= ($item->delivered_qty ?? 0);

            //increment the quantity (only the one that made the cut i.e. not out of order)
            $balance->qty_in += ($item->delivered_qty ?? 0) - ($item->rejected_qty ?? 0);;
            $balance->update();
        }
    }

    public function syncQtyInBalanceToB2B(GoodsReceiptNote $goodsReceiptNote)
    {
        foreach ($goodsReceiptNote->items as $item) {
            $balance = $item->product->balance;

            //increment the B2B quantity
            $balance->b2b_qty_in_pipeline += ($item->delivered_qty ?? 0);

            //decrement the quantity (only the one that made the cut i.e. not out of order)
            $balance->qty_in -= ($item->delivered_qty ?? 0) - ($item->rejected_qty ?? 0);
            $balance->update();
        }
    }


}
