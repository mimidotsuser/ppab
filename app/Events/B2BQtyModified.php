<?php

namespace App\Events;

use App\Models\StockBalance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class B2BQtyModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public StockBalance $balance;
    public int $by;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(StockBalance $balanceModel, int $updateBy)
    {
        $this->balance = $balanceModel;
        $this->by = $updateBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn(): Channel|PrivateChannel|array
    {
        return new PrivateChannel('channel-name');
    }
}
