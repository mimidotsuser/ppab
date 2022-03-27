<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductCheckout
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Product $product;
    public int $incrementBy;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Product $product, int $incrementBy)
    {
        $this->product = $product;
        $this->incrementBy = $incrementBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
