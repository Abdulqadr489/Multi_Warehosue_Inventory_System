<?php

namespace App\Providers;

use App\Events\LowStockReached;
use App\Listeners\SendLowStockNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LowStockReached::class => [
            SendLowStockNotification::class,
        ],
    ];
}
