<?php

namespace App\Repositories\Base;

use App\Repositories\Traits\SearchAndSort;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\AssignOp\Mod;

abstract class BaseRepository
{
    use SearchAndSort;

    protected Model $model;

    protected array $searchable = [];
    protected array $searchableRelations = [];
    protected array $sortable = ['id', 'created_at'];
    protected ?string $defaultSortBy = 'id';
    protected string $defaultSortDir = 'asc';
    protected array $with = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function paginate(int $perPage = 10)
    {
        return $this->paginateWithFilters([], $perPage);
    }

    public function paginateWithFilters(array $filters = [], int $perPage = 10)
    {
        $query = $this->buildQueryFilters($filters);

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->fill($data);
        $model->save();

        return $model;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
