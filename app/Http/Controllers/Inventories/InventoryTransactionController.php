<?php

namespace App\Http\Controllers\Inventories;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseList\BaseListRequest;
use App\Http\Requests\InventoryTransactions\CreateInventoryTransactionRequest;
use App\Models\Inventories\InventoryTransaction;
use App\Repositories\Inventories\InventoryTransactionRepository;
use App\Repositories\Traits\ApiResponse;
use App\Services\Inventories\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryTransactionController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ){}

    /**
     * List All Inventory Transactions
     *
     * You can:
     * - filter using `search` (`transaction_type` `quantity` `date` , `product name`,`warehouse name`,`supplier name`),
     * - sort using `sort_by` (`date`, `product_name`,`warehouse_name`),
     * - control direction with `sort_dir` (`asc`, `desc`),
     * - control page size with `per_page` (default 15).
     */
    public function index(BaseListRequest $request)
    {
        try {
            $filters=$request->filters();
            $per_page=$request->perPage();

            $transactions = $this->inventoryService->list($filters,$per_page);

            return $this->success($transactions,"Inventory transactions fetched successfully",200);

        }catch (\Exception $e){
            Log::error('Error listing inventory transactions', [
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Failed to fetch inventory transactions.',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * show transaction detail
     *
     * */
    public function show(InventoryTransaction $inventoryTransaction)
    {
        return $this->success(
            $inventoryTransaction->load(['product', 'warehouse', 'supplier', 'creator']),
            'Inventory transaction fetched successfully.'
        );
    }

    /**
     * create new transaction
     *
     * */
    public function store(CreateInventoryTransactionRequest $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validated();
            $transaction = $this->inventoryService->createTransaction($validated, $user);
            return $this->success($transaction,"Inventory transaction created successfully",200);
        }catch (\RuntimeException  $e){
            Log::error('Error adding inventory transaction', [
                'exception' => $e->getMessage(),
            ]);

            return $this->error(
                $e->getMessage(),
                422
            );
        }catch (\Throwable $e){
            Log::error('Error adding inventory transaction', [
                'exception' => $e->getMessage(),
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                $e->getMessage(),
                500
            );
        }
    }

}
