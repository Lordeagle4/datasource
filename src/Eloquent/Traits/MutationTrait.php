<?php

declare(strict_types=1);

namespace Awtechs\DataSource\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

trait MutationTrait
{
    /**
     * Wrap operations in a database transaction.
     *
     * @param \Closure $callback
     * @return mixed
     */
    public function transaction(\Closure $callback): mixed
    {
        return DB::transaction(function () use ($callback) {
            return $callback($this);
        });
    }

    /**
     * Create a new model instance.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        /** @var Model $model */
        $model = App::make($this->modelClass);
        $created = $model->create($data);
        if ($this->cacheResults) {
            $this->flushCache();
        }
        return $created;
    }

    /**
     * Update a model by ID.
     *
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update(mixed $id, array $data): bool
    {
        $model = $this->findOrFail($id);
        $updated = $model->update($data);
        if ($this->cacheResults) {
            $this->flushCache();
        }
        return $updated;
    }

    /**
     * Update or create a model.
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values): Model
    {
        /** @var Model $model */
        $model = App::make($this->modelClass);
        $result = $model->newQuery()->updateOrCreate($attributes, $values);
        if ($this->cacheResults) {
            $this->flushCache();
        }
        return $result;
    }

    /**
     * Upsert multiple rows.
     *
     * @param array $rows
     * @param array $uniqueBy
     * @param array|null $update
     * @return int
     */
    public function upsert(array $rows, array $uniqueBy, ?array $update = null): int
    {
        /** @var Model $model */
        $model = App::make($this->modelClass);
        $count = $model->newQuery()->upsert($rows, $uniqueBy, $update);
        if ($this->cacheResults) {
            $this->flushCache();
        }
        return $count;
    }

    /**
     * Delete a model by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function delete(mixed $id): bool
    {
        $model = $this->findOrFail($id);
        $deleted = (bool) $model->delete();
        if ($this->cacheResults) {
            $this->flushCache();
        }
        return $deleted;
    }

    /**
     * Force delete a model by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function forceDelete(mixed $id): bool
    {
        $model = $this->findOrFail($id);
        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($model), true);
        $deleted = $usesSoftDeletes ? (bool) $model->forceDelete() : (bool) $model->delete();
        if ($this->cacheResults) {
            $this->flushCache();
        }
        return $deleted;
    }

    /**
     * Restore a soft-deleted model by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function restore(mixed $id): bool
    {
        /** @var Model $model */
        $model = App::make($this->modelClass);

        if (!in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            return false;
        }

        $instance = $model->newQuery()->withTrashed()->findOrFail($id);
        $restored = (bool) $instance->restore();
        if ($this->cacheResults) {
            $this->flushCache();
        }
        return $restored;
    }
}