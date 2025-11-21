<?php

namespace App\Listeners;

use App\Events\LowStockReached;
use App\Mail\LowStockAlert\LowStockAlertMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLowStockNotification
{


    public function handle(LowStockReached $event): void
    {
        $inventory = $event->inventory;

        $to = config('low_stock.report_email');

        if ($to) {
            Mail::to($to)->send(new LowStockAlertMail($inventory));
        }

        Log::warning('Low stock alert triggered', [
            'product_id'   => $inventory->product_id,
            'warehouse_id' => $inventory->warehouse_id,
            'quantity'     => $inventory->quantity,
            'min_quantity' => $inventory->minimum_quantity,
        ]);
    }
}
