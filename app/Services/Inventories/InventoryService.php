<?php

namespace App\Services\Inventories;

use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryTransaction;
use App\Models\User;
use App\Repositories\Inventories\InventoryRepository;
use App\Repositories\Inventories\InventoryTransactionRepository;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\DB;

class InventoryService
{

    public function __construct(protected InventoryRepository $inventoryRepository,protected InventoryTransactionRepository $inventoryTransactionRepository)
    {

    }

    public function list(array $filters,int $per_page)
    {
        return $this->inventoryTransactionRepository->paginateWithFilters($filters,$per_page);
    }

    public function createTransaction(array $data,User $user)
    {
        return DB::transaction(function () use ($data, $user) {
           return  $this->CreateTransactionRecord($data,$user);
        });
    }


    public function transfer(array $data, User $user): array
    {
        return DB::transaction(function () use ($data, $user) {

            $productId  = $data['product_id'];
            $fromId     = $data['from_warehouse_id'];
            $toId       = $data['to_warehouse_id'];
            $quantity   = (float) $data['quantity'];
            $date       = $data['date'] ?? now();
            $supplierId = $data['supplier_id'] ?? null;
            $sourceMinQty =  $data['minimum_quantity'];

            $outTransaction = $this->CreateTransactionRecord([
                'product_id'       => $productId,
                'warehouse_id'     => $fromId,
                'supplier_id'      => $supplierId,
                'quantity'         => $quantity,
                'transaction_type' => 'OUT',
                'date'             => $date,
                'minimum_quantity' => $sourceMinQty,
            ], $user);

            $inTransaction = $this->CreateTransactionRecord([
                'product_id'       => $productId,
                'warehouse_id'     => $toId,
                'supplier_id'      => $supplierId,
                'quantity'         => $quantity,
                'transaction_type' => 'IN',
                'date'             => $date,
                'minimum_quantity'  => $sourceMinQty,

            ], $user);

            return [
                'from_transaction' => $outTransaction,
                'to_transaction'   => $inTransaction,
            ];
        });
    }

    protected function CreateTransactionRecord(array $data, User $user)
    {
        $productId   = $data['product_id'];
        $warehouseId = $data['warehouse_id'];
        $quantity    = (float) $data['quantity'];
        $type        = strtoupper($data['transaction_type']);
        $date        = $data['date'] ?? now();
        $supplierId  = $data['supplier_id'] ?? null;
        $minimumQuantity = (float) $data['minimum_quantity'];
        $inventory = $this->inventoryRepository->findProductOrUpdate($productId, $warehouseId);

        if ($type === 'IN') {
            if (!$inventory) {
                $inventory = $this->inventoryRepository
                    ->CreateInventory(
                        $productId,
                        $warehouseId,
                        $quantity,
                        $minimumQuantity
                    );
            } else {
                $inventory->minimum_quantity = $minimumQuantity;
                $inventory->quantity += $quantity;
                $inventory->save();
            }
        } elseif ($type === 'OUT') {
            if (!$inventory || $inventory->quantity < $quantity) {
                throw new \RuntimeException('Insufficient stock in this warehouse.');
            }

            $inventory->quantity -= $quantity;
            $inventory->minimum_quantity = $data['minimum_quantity'] ?? 0;
            $inventory->save();
        } else {
            throw new \InvalidArgumentException('Invalid transaction type, must be IN or OUT.');
        }

        $transaction = InventoryTransaction::create([
            'product_id'       => $productId,
            'warehouse_id'     => $warehouseId,
            'supplier_id'      => $supplierId,
            'quantity'         => $quantity,
            'transaction_type' => $type,
            'date'             => $date,
            'created_by'       => $user->id,
        ]);

        return $transaction->load(['product', 'warehouse', 'supplier']);
    }

}
