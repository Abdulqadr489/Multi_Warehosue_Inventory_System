<?php

namespace App\Http\Controllers\Inventories;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseList\BaseListRequest;
use App\Http\Requests\Inventories\InventoryGlobalViewRequest;
use App\Repositories\Traits\ApiResponse;
use App\Services\Inventories\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryReportController extends Controller
{
    use ApiResponse;

    public function __construct(protected InventoryService $inventoryService)
    {}

    public function globalView(InventoryGlobalViewRequest $request)
    {
        try {
            $filters = $request->filters();
            $perPage = $request->perPage();

            $result = $this->inventoryService->globalView($filters, $perPage);

            return $this->success($result, 'Global inventory view fetched successfully.');

        } catch (\Throwable $e) {
            \Log::error('Error fetching global inventory view', [
                'error' => $e->getMessage(),
            ]);
            return $this->error('Failed to fetch global inventory view.', 500, $e->getMessage()
            );
        }
    }

    public function lowStock()
    {
        try {
            $data = $this->inventoryService->getLowStockProduct();
            return $this->success($data, 'Low inventory view fetched successfully.');
        }catch (\Throwable $e){
            \Log::error('Error fetching low stock inventory view', [
                'error' => $e->getMessage(),

            ]);
            return $this->error('Failed to fetch low stock inventory view.', 500, $e->getMessage());
        }
    }
}
