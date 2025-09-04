<?php

declare(strict_types=1);

namespace Awtechs\DataSource\Eloquent\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

trait RetrievalTrait
{
    /**
     * Reset the builder to a fresh state.
     *
     * @return static
     */
    public function reset(): static
    {
        /** @var Model $model */
        $model = App::make($this->modelClass);
        $this->builder = $model->newQuery();
        $this->cacheTtlPerChain = null; // Reset per-chain cache
        return $this;
    }

    /**
     * Retrieve all models.
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        $result = $this->remember('all', [$columns], fn() => $this->builder->get($columns));
        $this->reset();
        return $result;
    }

    /**
     * Paginate the results.
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $perPage = $perPage ?: $this->defaultPerPage;
        $result = $this->remember('paginate', [$perPage, $columns], fn() => $this->builder->paginate($perPage, $columns));
        $this->reset();
        return $result;
    }

    /**
     * Find a model by ID.
     *
     * @param mixed $id
     * @param array $columns
     * @return Model|null
     */
    public function find(mixed $id, array $columns = ['*']): ?Model
    {
        /** @var Model $model */
        $model = App::make($this->modelClass);
        return $model->newQuery()->find($id, $columns);
    }

    /**
     * Find a model by ID or fail.
     *
     * @param mixed $id
     * @param array $columns
     * @return Model
     */
    public function findOrFail(mixed $id, array $columns = ['*']): Model
    {
        /** @var Model $model */
        $model = App::make($this->modelClass);
        return $model->newQuery()->findOrFail($id, $columns);
    }

    /**
     * Find the first model matching conditions.
     *
     * @param array $conditions
     * @param array $columns
     * @return Model|null
     */
    public function firstWhere(array $conditions, array $columns = ['*']): ?Model
    {
        $q = clone $this->builder;
        foreach ($conditions as $col => $val) {
            $q->where($col, $val);
        }
        $result = $this->remember('firstWhere', [$conditions, $columns], fn() => $q->first($columns));
        $this->reset();
        return $result;
    }
}