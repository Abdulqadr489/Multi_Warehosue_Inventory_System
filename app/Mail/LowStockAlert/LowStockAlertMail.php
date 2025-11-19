<?php

namespace App\Mail\LowStockAlert;

use App\Models\Inventories\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public Inventory $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public function build()
    {
        return $this->subject('Low Stock Alert')
            ->view('emails.low_stock_alert')
            ->with([
                'inventory' => $this->inventory->load(['product', 'warehouse.country']),
            ]);
    }

}
