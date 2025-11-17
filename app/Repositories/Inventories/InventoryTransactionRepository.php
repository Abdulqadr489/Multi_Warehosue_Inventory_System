<?php

namespace App\Repositories\Inventories;

use App\Http\Requests\BaseList\BaseListRequest;
use App\Models\Inventories\InventoryTransaction;
use App\Repositories\Base\BaseRepository;

class InventoryTransactionRepository extends BaseRepository
{
    public function __construct(InventoryTransaction $model)
    {
        parent::__construct($model);

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
}
