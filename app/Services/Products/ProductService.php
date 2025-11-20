<?php

namespace App\Services\Products;

use App\Models\Product\Product;
use App\Repositories\Products\ProductRepository;
use App\Repositories\Warehouses\WarehouseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(Protected ProductRepository $productRepository)
    {
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->productRepository->paginateWithFilters($filters, $perPage);
    }

    public function find(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    public function create(array $data)
    {
       return DB::transaction(function () use ($data) {
            return $this->productRepository->create($data);
        });

    }

    public function update(Product $product, array $data):Product
    {
        return DB::transaction(function () use ($product, $data) {
           return $this->productRepository->update($product, $data);
        });
    }

    public function delete(Product $product){
        return DB::transaction(function () use ($product){
            return $this->productRepository->delete($product);
        });
    }
}
