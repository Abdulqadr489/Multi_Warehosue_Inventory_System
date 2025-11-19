<?php

namespace App\Console\Commands;

use App\Mail\LowStockReport\LowStockReportMail;
use App\Models\Product\Product;
use App\Notifications\LowStockSlackNotification;
use App\Services\Inventories\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class CheckLowStockInventory extends Command
{
    protected $signature = 'inventory:check-low-stock';

    public $description = 'Scan inventories and send low stock report email with Slack notification';


    public function handle(InventoryService $inventoryService)
    {
        $products = $inventoryService->getLowStockProduct();

        if($products->isEmpty()){
            $this->info('There is no low inventory report');
            return Command::SUCCESS;
        }

        $to = env('LOW_STOCK_REPORT_EMAIL');

        if(!$to)
        {
            $this->warn('Low inventory report email not sent');
            return Command::SUCCESS;
        }
        else{
            Mail::to($to)->send(new LowStockReportMail($products));
            $this->info('Low inventory report email sent to '.$to);
        }

        $slackWebhook = env('LOW_STOCK_SLACK_WEBHOOK');

        if ($slackWebhook) {
            try {
                Notification::route('slack', $slackWebhook)
                    ->notify(new LowStockSlackNotification($products));

                $this->info('Low inventory Slack notification sent.');
            } catch (\Throwable $e) {
                $this->warn('Slack notification failed: ' . $e->getMessage());
                \Log::error('Slack low stock notification error', [
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->warn('Slack notification not sent (LOW_STOCK_SLACK_WEBHOOK not configured)');
        }

        return Command::SUCCESS;
        }
}
