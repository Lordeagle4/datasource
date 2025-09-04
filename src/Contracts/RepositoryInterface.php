<?php

declare(strict_types=1);

namespace Awtechs\DataSource\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract for repository implementations handling Eloquent model interactions.
 */
interface RepositoryInterface
{
    /**
     * Specify the Eloquent model class for the repository.
     *
     * @return class-string<Model>|null
     */
    public static function model(): ?string;

    /**
     * Get the underlying query builder instance.
     *
     * @return Builder
     */
    public function query(): Builder;

    /**
     * Retrieve all models.
     *
     * @param array $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Paginate the results.
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find a model by ID.
     *
     * @param mixed $id
     * @param array $columns
     * @return Model|null
     */
    public function find(mixed $id, array $columns = ['*']): ?Model;

    /**
     * Find a model by ID or fail.
     *
     * @param mixed $id
     * @param array $columns
     * @return Model
     */
    public function findOrFail(mixed $id, array $columns = ['*']): Model;

    /**
     * Find the first model matching conditions.
     *
     * @param array $conditions
     * @param array $columns
     * @return Model|null
     */
    public function firstWhere(array $conditions, array $columns = ['*']): ?Model;

    /**
     * Create a new model instance.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update a model by ID.
     *
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    public function update(mixed $id, array $data): bool;

    /**
     * Update or create a model.
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values): Model;

    /**
     * Upsert multiple rows.
     *
     * @param array $rows
     * @param array $uniqueBy
     * @param array|null $update
     * @return int
     */
    public function upsert(array $rows, array $uniqueBy, ?array $update = null): int;

    /**
     * Delete a model by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function delete(mixed $id): bool;

    /**
     * Force delete a model by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function forceDelete(mixed $id): bool;

    /**
     * Restore a soft-deleted model by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function restore(mixed $id): bool;

    /**
     * Eager load relations on the model.
     *
     * @param array|string $relations
     * @return static
     */
    public function with(array|string $relations): static;

    /**
     * Order the query by a given column and direction.
     *
     * @param string $column
     * @param string $direction
     * @return static
     */
    public function orderBy(string $column, string $direction = 'asc'): static;

    /**
     * Add where conditions to the query.
     *
     * @param array $conditions
     * @return static
     */
    public function where(array $conditions): static;

    /**
     * Search across multiple columns with a keyword.
     *
     * @param array $columns
     * @param string $keyword
     * @return static
     */
    public function search(array $columns, string $keyword): static;

    /**
     * Conditionally apply a callback to the query.
     *
     * @param bool $condition
     * @param callable $callback
     * @return static
     */
    public function when(bool $condition, callable $callback): static;

    /**
     * Enable or disable caching for repository operations.
     *
     * @param bool $enabled
     * @return static
     */
    public function cacheResults(bool $enabled): static;

    /**
     * Set the default cache duration in seconds.
     *
     * @param int $seconds
     * @return static
     */
    public function cacheDuration(int $seconds): static;

    /**
     * Enable caching for the current query chain with a specific TTL.
     *
     * @param int $seconds
     * @return static
     */
    public function cacheFor(int $seconds): static;

    /**
     * Reset the query builder to a fresh state.
     *
     * @return static
     */
    public function reset(): static;

    /**
     * Wrap operations in a database transaction.
     *
     * @param \Closure $callback
     * @return mixed
     */
    public function transaction(\Closure $callback): mixed;
}
