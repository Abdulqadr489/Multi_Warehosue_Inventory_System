<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\SlackMessage;

class LowStockSlackNotification extends Notification
{
    use Queueable;


    public function __construct(protected $products)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['slack'];
    }
    public function toSlack(object $notifiable): SlackMessage
    {
        $count = $this->products->count();

        $lines = [];

        foreach ($this->products->take(5) as $product) {
            $lines[] = sprintf(
                '- %s (%s): %s / %s in %s (%s)',
                $product['product_name']      ?? '-',
                $product['sku']               ?? '-',
                $product['current_quantity']  ?? '-',
                $product['minimum_required']  ?? '-',
                $product['warehouse_name']    ?? '-',
                $product['country']           ?? '-',
            );
        }

        $text = "Low stock report: {$count} product(s) at or below minimum.\n"
            . implode("\n", $lines);

        return (new SlackMessage)
            ->text($text);
    }

}
