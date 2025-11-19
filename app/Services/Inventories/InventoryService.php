<?php

namespace App\Services\Inventories;

use App\Events\LowStockReached;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryTransaction;
use App\Models\User;
use App\Repositories\Inventories\InventoryRepository;
use App\Repositories\Inventories\InventoryTransactionRepository;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\DB;

class InventoryService
{

    public function __construct(protected InventoryRepository $inventoryRepository, protected InventoryTransactionRepository $inventoryTransactionRepository)
    {}

    public function list(array $filters, int $per_page)
    {
        return $this->inventoryTransactionRepository->paginateWithFilters($filters, $per_page);
    }

    //create transfer between warehouses
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


    //create Transaction between per warehouse
    public function createTransaction(array $data,User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            return  $this->CreateTransactionRecord($data,$user);
        });
    }

    protected function CreateTransactionRecord(array $data, User $user)
    {
        $productId = $data['product_id'];
        $warehouseId = $data['warehouse_id'];
        $quantity = (float)$data['quantity'];
        $type = strtoupper($data['transaction_type']);
        $date = $data['date'] ?? now();
        $supplierId = $data['supplier_id'] ?? null;
        $minimumQuantity = (float)$data['minimum_quantity'];

        if (!in_array($type, ['IN', 'OUT'], true)) {
            throw new \InvalidArgumentException('Invalid transaction type, must be IN or OUT.');
        }

        $inventory = $this->inventoryRepository->findProductOrUpdate($productId, $warehouseId);

        if (!$inventory) {
            $inventory = $this->inventoryRepository->CreateInventory(
                $productId,
                $warehouseId,
                0,                 // start at 0, then adjust via IN/OUT
                $minimumQuantity
            );
        }
        $inventory->minimum_quantity = $minimumQuantity;
        $inventory->save();

        $this->adjustInventory($inventory, $type, $quantity);

        $transaction = InventoryTransaction::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'supplier_id' => $supplierId,
            'quantity' => $quantity,
            'transaction_type' => $type,
            'date' => $date,
            'created_by' => $user->id,
        ]);

        return $transaction->load(['product', 'warehouse', 'supplier', 'warehouse.country', 'creator']);

    }

    //get all total stock per product in all warehouses
    public function globalView(array $filters = [], int $perPage = 15)
    {
        $query = Inventory::query()
            ->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id');

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['warehouse_name'])) {
            $name = $filters['warehouse_name'];
            $query->whereHas('warehouse', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
        }

        if (!empty($filters['country_id']) || !empty($filters['country_name'])) {
            $countryId   = $filters['country_id']   ?? null;
            $countryName = $filters['country_name'] ?? null;

            $query->whereHas('warehouse.country', function ($q) use ($countryId, $countryName) {
                if (!empty($countryId)) {
                    $q->where('id', $countryId);
                }
                if (!empty($countryName)) {
                    $q->where('name', 'like', '%' . $countryName . '%');
                }
            });
        }
        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(function ($row) {
            return [
                'product_id'     => $row->product_id,
                'product_name'   => $row->product?->name,
                'product_sku'    => $row->product?->sku,
                'total_quantity' => (float) $row->total_quantity,
            ];
        });

        return $paginated;
    }

    public function lowStockQuery()
    {
        return Inventory::query()
            ->with([
                'product',
                'warehouse.country',
            ])
            ->whereColumn('quantity','<=','minimum_quantity');
    }

    public function getLowStockProduct()
    {
        $inventories = $this->lowStockQuery()->get();

        if ($inventories->isEmpty()) {
            return collect();
        }

        return $inventories->map(function (Inventory $inventory) {
            $product   = $inventory->product;
            $warehouse = $inventory->warehouse;
            $country   = $warehouse?->country;

            $supplierName   = null;
            $supplierContact = null;

            if ($inventory->product_id && $inventory->warehouse_id) {
                $lastInTransaction = InventoryTransaction::query()
                    ->with('supplier')
                    ->where('product_id', $inventory->product_id)
                    ->where('warehouse_id', $inventory->warehouse_id)
                    ->where('transaction_type', 'IN')
                    ->whereNotNull('supplier_id')
                    ->orderByDesc('date')
                    ->first();

                if ($lastInTransaction && $lastInTransaction->supplier) {
                    $supplierName    = $lastInTransaction->supplier->name;
                    $supplierContact = $lastInTransaction->supplier->contact_info;
                }
            }

            return [
                'product_name'            => $product?->name,
                'sku'                     => $product?->sku,
                'current_quantity'        => (float) $inventory->quantity,
                'minimum_required'        => (float) $inventory->minimum_quantity,
                'warehouse_name'          => $warehouse?->name,
                'warehouse_location'      => $warehouse?->location,
                'country'                 => $country?->name,
                'country_code'            => $country?->code,
                'supplier_name'           => $supplierName,
                'supplier_contact_info'   => $supplierContact,
            ];
        });


    }

    protected function adjustInventory(Inventory $inventory, string $type, float $quantity): Inventory
    {
        if ($type === 'IN') {
            $inventory->quantity += $quantity;
        } else {
            if ($inventory->quantity < $quantity) {
                throw new \RuntimeException('Insufficient stock in this warehouse.');
            }
            $inventory->quantity -= $quantity;
        }

        $inventory->save();

        if ($inventory->quantity <= $inventory->minimum_quantity) {
            LowStockReached::dispatch(
                $inventory->fresh(['product', 'warehouse.country'])
            );
        }

        return $inventory;
    }
}
