<?php

namespace App\Repositories\Suppliers;

use App\Models\Suppliers\Supplier;
use App\Repositories\Base\BaseRepository;

class SupplierRepository extends BaseRepository
{
    public function __construct(Supplier $supplier)
    {
        Parent::__construct($supplier);

        $this->searchable = ['name', 'address'];

        $this->sortable = ['name', 'address'];

        $this->defaultSortBy = 'name';

    }
}
