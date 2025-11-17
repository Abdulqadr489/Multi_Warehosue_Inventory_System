<?php

namespace App\Services\Warehouses;

use App\Models\Warehouses\Warehouse;
use App\Repositories\Warehouses\WarehouseRepository;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    public function __construct(protected WarehouseRepository $warehouseRepository)
    {

    }
    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->warehouseRepository->paginateWithFilters($filters, $perPage);
    }

    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            return $this->warehouseRepository->create($data);
        });
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            $this->warehouseRepository->update($warehouse, $data);

            return $warehouse->fresh();
        });
    }

    public function delete(Warehouse $warehouse)
    {
        return DB::transaction(function () use ($warehouse) {
            return $this->warehouseRepository->delete($warehouse);
        });
    }

}
