<?php

namespace App\Repositories\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchAndSort
{
    protected function buildQueryFilters(array $filters = []): Builder
    {
        $query = $this->model->newQuery();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        $this->applySearch($query, $filters);
        $this->applySort($query, $filters);

        return $query;
    }

    protected function applySearch(Builder $query, array $filters): void
    {
        if (empty($filters['search'])) {
            return;
        }

        $search = $filters['search'];

        $query->where(function (Builder $q) use ($search) {
            if (!empty($this->searchable)) {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            }

            if (!empty($this->searchableRelations)) {
                foreach ($this->searchableRelations as $relation => $columns) {
                    $q->orWhereHas($relation, function (Builder $relQuery) use ($columns, $search) {
                        foreach ($columns as $column) {
                            $relQuery->orWhere($column, 'like', '%' . $search . '%');
                        }
                    });
                }
            }
        });
    }

    protected function applySort(Builder $query, array $filters): void
    {
        $sortBy  = $filters['sort_by'] ?? $this->defaultSortBy;
        $sortDir = strtolower($filters['sort_dir'] ?? $this->defaultSortDir);

        if (!$sortBy || !in_array($sortBy, $this->sortable, true)) {
            return;
        }

        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortDir);
    }
}
