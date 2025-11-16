<?php

namespace App\Repositories\Countries;

use App\Models\Countries\Country;
use App\Repositories\Base\BaseRepository;

class CountryRepository extends BaseRepository
{

    protected array $searchable = ['name', 'code'];

    protected array $sortable = ['id', 'name', 'code', 'created_at'];

    public function  __construct(Country $model)
    {
        Parent::__construct($model);
    }
}
