<?php

namespace App\Services\Suppliers;

use App\Models\Suppliers\Supplier;
use App\Repositories\Suppliers\SupplierRepository;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function __construct(protected SupplierRepository $supplierRepository)
    {

    }

    public function list(array $filters=[],$per_page=10)
    {
       return $this->supplierRepository->paginateWithFilters($filters,$per_page);

    }

    public function create(array $data) : Supplier
    {
        return DB::transaction(function () use ($data) {
            return  $this->supplierRepository->create($data);
        });
    }



    public function update(Supplier $supplier, array $data) : Supplier
    {
        return DB::transaction(function () use ($supplier, $data){
            return $this->supplierRepository->update($supplier, $data);
        });

    }

    public function delete(Supplier $supplier) : Supplier
    {
        return DB::transaction(function () use ($supplier){
              $this->supplierRepository->delete($supplier);
        });
    }
}
