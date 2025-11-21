<?php

namespace App\Console\Commands;

use App\Mail\LowStockReport\LowStockReportMail;
use App\Models\Product\Product;
use App\Notifications\LowStockSlackNotification;
use App\Services\Inventories\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CheckLowStockInventory extends Command
{
    protected $signature = 'inventory:check-low-stock';

    public $description = 'Scan inventories and send low stock report email with Slack notification';

    public function handle(InventoryService $inventoryService)
    {
        $products = $inventoryService->getLowStockProduct();

        if ($products->isEmpty()) {
            $this->info('There is no low inventory report');
            return CommandAlias::SUCCESS;
        }

        $to = config('low_stock.report_email');

        if (!$to) {
            $this->warn('Low inventory report email not sent (LOW_STOCK_REPORT_EMAIL missing)');
            return CommandAlias::SUCCESS;
        }

        Mail::to($to)->send(new LowStockReportMail($products));
        $this->info('Low inventory report email sent to ' . $to);

        $webhook = config('low_stock.slack_webhook');

        if ($webhook) {
            try {
                $count = $products->count();

                $lines = $products->take(5)->map(function ($item) {
                    return sprintf(
                        '- %s (%s): qty=%s, warehouse=%s',
                        $item['product_name'] ?? 'N/A',
                        $item['sku'] ?? 'N/A',
                        $item['current_quantity'] ?? '0',
                        $item['warehouse_name'] ?? 'N/A'
                    );
                })->implode("\n");

                Http::withOptions(['verify' => false])
                ->post($webhook, [
                    'text' => "Low stock report: {$count} products at or below minimum.\n\n{$lines}",
                ]);

                $this->info('Slack notification sent (with SSL verify=false for local dev)');
            } catch (\Throwable $e) {
                $this->warn('Slack notification failed (local SSL issue): ' . $e->getMessage());
            }
        } else {
            $this->warn('LOW_STOCK_SLACK_WEBHOOK not configured, skipping Slack notification.');
        }

        return CommandAlias::SUCCESS;
    }

}
