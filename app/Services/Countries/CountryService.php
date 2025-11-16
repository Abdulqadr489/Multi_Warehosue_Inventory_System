<?php

namespace App\Services\Countries;

use App\Models\Countries\Country;
use App\Repositories\Countries\CountryRepository;
use App\Repositories\Countries\CountryRepositoryInterface;

class CountryService
{
    public function __construct(protected CountryRepository $countries)
    {

    }
    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->countries->paginateWithFilters($perPage, $filters);
    }

    public function create(array $data)
    {
        return $this->countries->create($data);
    }

    public function update(Country $country, array $data)
    {
         return $this->countries->update($country, $data);
    }

    public function delete(Country $country):void
    {
        $this->countries->delete($country);
    }

}
