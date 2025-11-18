<?php

namespace App\Repositories\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchAndSort
{
    protected function buildQueryFilters(array $filters = []): Builder
    {
        $query = $this->model->newQuery();

        if(!empty($this->select)){
            $query->select($this->select);
        }
        if (!empty($this->with)) {
            $query->with($this->with);
        }

        $this->applySearch($query, $filters);
        $this->applySort($query, $filters);


        return $query;
    }

    protected function applySearch(Builder $query, array $filters): void
    {
        if (!isset($filters['search']) || $filters['search'] === '') {
            return;
        }

        $search = $filters['search'];


        $query->where(function (Builder $q) use ($search) {
            // main model columns
            if (!empty($this->searchable)) {
                $first = true;
                foreach ($this->searchable as $column) {
                    if ($first) {
                        $q->where($column, 'like', '%' . $search . '%');
                        $first = false;
                    } else {
                        $q->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            }

            if (!empty($this->searchableRelations)) {
                foreach ($this->searchableRelations as $relation => $columns) {
                    \Log::info('SEARCH RELATION', [
                        'relation' => $relation,
                        'columns'  => $columns,
                        'search'   => $search,
                    ]);

                    $q->orWhereHas($relation, function (Builder $relQuery) use ($columns, $search) {
                        $relQuery->where(function (Builder $relQ) use ($columns, $search) {
                            $firstRel = true;
                            foreach ($columns as $column) {
                                if ($firstRel) {
                                    $relQ->where($column, 'like', '%' . $search . '%');
                                    $firstRel = false;
                                } else {
                                    $relQ->orWhere($column, 'like', '%' . $search . '%');
                                }
                            }
                        });
                    });
                }
            }
        });
    }

    protected function applySort(Builder $query, array $filters): void
    {
        $sortBy  = $filters['sort_by'] ?? $this->defaultSortBy;
        $sortDir = strtolower($filters['sort_dir'] ?? $this->defaultSortDir);

        if (!$sortBy) {
            return;
        }

        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        if (!empty($this->sortableRelations) && array_key_exists($sortBy, $this->sortableRelations)) {
            [$relation, $column] = $this->sortableRelations[$sortBy];

            $relationInstance = $this->model->{$relation}();
            $relatedTable = $relationInstance->getRelated()->getTable();
            $parentTable  = $this->model->getTable();

            $foreignKey = $relationInstance->getQualifiedForeignKeyName();
            $ownerKey   = $relationInstance->getQualifiedOwnerKeyName();

            $query->leftJoin($relatedTable, $foreignKey, '=', $ownerKey)
                ->select($parentTable . '.*')
                ->orderBy($relatedTable . '.' . $column, $sortDir);

            return;
        }

        if (!in_array($sortBy, $this->sortable, true)) {
            return;
        }

        $query->orderBy($sortBy, $sortDir);
    }
}
