<?php

namespace App\Repositories\Products;

use App\Models\Product\Product;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);

        $this->searchable = ['name', 'sku', 'status','price'];


        $this->sortable = ['name', 'sku', 'status','price'];

        $this->defaultSortBy = 'name';

    }

    protected function cacheTtl(): int
    {
        return (int) env('PRODUCT_CACHE_TTL', 600);
    }


    public function find(int $id): ?Model
    {
        $ttl = $this->cacheTtl();

        return Cache::remember("product:{$id}", $ttl, function () use ($id) {
            \Log::info('DB HIT in ProductRepository::find', ['id' => $id]); // 👈 add this

            return $this->model->newQuery()->find($id);
        });
    }

    public function update(Model $model, array $data): Model
    {
        $model = parent::update($model, $data);

        Cache::forget("product:{$model->id}");

        return $model;
    }


    public function delete(Model $model): void
    {
        parent::delete($model);

        Cache::forget("product:{$model->id}");
    }
}
