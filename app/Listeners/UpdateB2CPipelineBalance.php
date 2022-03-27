<?php

namespace App\Listeners;

class UpdateB2CPipelineBalance
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
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $stockBalance = $event->product->balance;
        $stockBalance->b2c_qty_in_pipeline += $event->incrementBy;
        $stockBalance->update();
    }
}
