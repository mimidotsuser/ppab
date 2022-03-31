<?php

namespace App\Listeners;

use App\Events\B2BQtyModified;

class UpdateB2BPipelineBalance
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
     * @param B2BQtyModified $event
     * @return void
     */
    public function handle(B2BQtyModified $event)
    {
        $event->balance->b2b_qty_in_pipeline += $event->by;
        $event->balance->update();
    }
}
