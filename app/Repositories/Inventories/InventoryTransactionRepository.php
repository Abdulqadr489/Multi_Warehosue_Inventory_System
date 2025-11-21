<?php

namespace App\Repositories\Inventories;

use App\Models\Inventories\InventoryTransaction;
use App\Repositories\Base\BaseRepository;

class InventoryTransactionRepository extends BaseRepository
{
    public function __construct(InventoryTransaction $model)
    {
        parent::__construct($model);

        $this->select = [
            'id',
            'product_id',
            'warehouse_id',
            'supplier_id',
            'quantity',
            'transaction_type',
            'date',
            'created_by',
        ];

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
            'quantity',
            'created_at',
        ];

        $this->defaultSortBy  = 'date';
        $this->defaultSortDir = 'desc';

        $this->with = [
            'product:id,name,sku',
            'warehouse:id,name,location,country_id',
            'warehouse.country:id,name,code',
            'supplier:id,name,contact_info',
            'creator:id,name,email',
        ];


        $this->sortableRelations = [
            'product_name'   => ['product', 'name'],
            'warehouse_name' => ['warehouse', 'name'],
        ];
    }

}
