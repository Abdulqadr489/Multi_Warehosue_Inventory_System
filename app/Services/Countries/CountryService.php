<?php

namespace App\Services\Countries;

use App\Models\Countries\Country;
use App\Repositories\Countries\CountryRepository;
use App\Repositories\Countries\CountryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CountryService
{
    public function __construct(protected CountryRepository $countries)
    {

    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->countries->paginateWithFilters($filters,$perPage);
    }

    public function create(array $data): Country
    {
        return DB::transaction(function () use ($data) {
            return $this->countries->create($data);
        });
    }

    public function update(Country $country, array $data): Country
    {
        return DB::transaction(function () use ($country, $data) {
            $this->countries->update($country, $data);

            return $country->fresh();
        });
    }

    public function delete(Country $country)
    {
        return DB::transaction(function () use ($country) {
            $this->countries->delete($country);
        });
    }

}
