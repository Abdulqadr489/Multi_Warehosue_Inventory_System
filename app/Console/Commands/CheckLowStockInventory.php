<?php

namespace App\Console\Commands;

use App\Mail\LowStockReport\LowStockReportMail;
use App\Models\Product\Product;
use App\Services\Inventories\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStockInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock';

    public $description = 'Scan inventories and send low stock report email';


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

        Mail::to($to)->send(new LowStockReportMail($products));

        $this->info('Low inventory report email sent to '.$to);

        return Command::SUCCESS;
        }
}
