<?php

namespace App\Repositories\Warehouses;

use App\Models\Warehouses\Warehouse;
use App\Repositories\Base\BaseRepository;
use Faker\Provider\Base;

class WarehouseRepository extends BaseRepository
{

    public function __construct(Warehouse $model)
    {
        parent::__construct($model);

        $this->searchable = ['name', 'location', 'country_id'];

        $this->searchableRelations = [
            'country' => ['name', 'code'],
        ];

        $this->sortable = ['id', 'name', 'location', 'created_at'];

        $this->sortableRelations = [
            'country_name' => ['country', 'name'],
        ];

        $this->defaultSortBy = 'name';

        $this->with = ['country'];
    }

}
