<?php

namespace App\Repositories\Inventories;

use App\Models\Inventories\Inventory;
use App\Repositories\Base\BaseRepository;

class InventoryRepository extends BaseRepository
{
    public function __construct(Inventory $inventory)
    {
        parent::__construct($inventory);

        $this->searchable = [
            'transaction_type',
            'quantity',
            'date',
        ];

        $this->searchableRelations = [
            'product'   => ['name', 'sku'],
            'warehouse' => ['name', 'location'],
            'supplier'  => ['name'],
        ];

        $this->sortable = [
            'id',
            'date',
        ];

        $this->defaultSortBy  = 'date';
        $this->defaultSortDir = 'desc';

        $this->with = [
            'product',
            'warehouse.country',
            'supplier',
            'creator',
        ];

        $this->sortableRelations = [
            'product_name'   => ['product', 'name'],
            'warehouse_name' => ['warehouse', 'name'],
        ];

    }

    public function findProductOrUpdate(int $product_id,int $warehouse_id)
    {
        return $this->model
            ->where('product_id',$product_id)
            ->where('warehouse_id',$warehouse_id)
            ->lockForUpdate()
            ->first();
    }

    public function CreateInventory(int $product_id,int $warehouse_id,$quantity,$minimum_quantity)
    {
        return $this->model->create([
            'product_id' => $product_id,
            'warehouse_id' => $warehouse_id,
            'quantity' => $quantity,
            'minimum_quantity' => $minimum_quantity,
        ]);
    }
}
