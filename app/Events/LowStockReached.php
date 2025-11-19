<?php

namespace App\Events;

use App\Models\Inventories\Inventory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockReached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Inventory $inventory;
    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

}
