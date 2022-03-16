<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductItemUpsert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Product $product;
    public int $incrementBy;

    /**
     * Create a new event instance.
     *
     * @param Product $product
     * @param int $incrementBy
     */
    public function __construct(Product $product, int $incrementBy)
    {
        $this->product = $product;
        $this->incrementBy = $incrementBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
