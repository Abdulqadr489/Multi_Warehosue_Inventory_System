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
    use ApiResponse;

    public function __construct(
        protected InventoryService $inventoryService,
        protected InventoryTransactionRepository $inventoryTransactionRepository
    ){}

    public function index(BaseListRequest $request)
    {
        try {
            $validated = $request->validated();
            $filters=$request->filters();
            $per_page=$request->perPage();

            $transactions = $this->inventoryTransactionRepository->paginateWithFilters($filters,$per_page);

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


    public function show(InventoryTransaction $inventoryTransaction)
    {
        //
    }

    public function edit(InventoryTransaction $inventoryTransaction)
    {
        //
    }

    public function update(Request $request, InventoryTransaction $inventoryTransaction)
    {
        //
    }


    public function destroy(InventoryTransaction $inventoryTransaction)
    {
        //
    }
}
