<?php

namespace App\Listeners;

use App\Events\ProductCheckout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateStockOutBalance
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
     * @param  \App\Events\ProductCheckout  $event
     * @return void
     */
    public function handle(ProductCheckout $event)
    {
        $stockBalance = $event->product->balance;
        $stockBalance->qty_out += $event->incrementBy;
        $stockBalance->update();
    }
}
