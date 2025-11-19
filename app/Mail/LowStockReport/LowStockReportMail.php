<?php

namespace App\Mail\LowStockReport;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $products;

    public function __construct(Collection $products)
    {
        $this->products = $products;
    }

    public function build()
    {
        return $this->subject('Low Stock Report')
            ->view('emails.low_stock_report')
            ->with([
                'products' => $this->products,
            ]);
    }
}
