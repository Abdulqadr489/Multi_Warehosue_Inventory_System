<?php

namespace App\Repositories\Products;

use App\Models\Product\Product;
use App\Repositories\Base\BaseRepository;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);

        $this->searchable = ['name', 'sku', 'status','price'];


        $this->sortable = ['name', 'sku', 'status','price'];

        $this->defaultSortBy = 'name';

    }
}
