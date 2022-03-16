<?php

namespace App\Listeners;

use App\Events\ProductItemUpsert;

class UpdateStockInBalance
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ProductItemUpsert $event
     * @return void
     */
    public function handle(ProductItemUpsert $event)
    {
        $stockBalance = $event->product->balance;
        $stockBalance->qty_in += $event->incrementBy;
        $stockBalance->update();
    }
}
