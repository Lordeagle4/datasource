<?php

declare(strict_types=1);

namespace Awtechs\DataSource\Eloquent\Traits;

use Illuminate\Database\Eloquent\Builder;

trait QueryBuilderTrait
{
    protected Builder $builder;

    /**
     * Get the underlying query builder instance.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->builder;
    }

    /**
     * Eager load relations on the model.
     *
     * @param array|string $relations
     * @return static
     */
    public function with(array|string $relations): static
    {
        $this->builder->with($relations);
        return $this;
    }

    /**
     * Order the query by a given column and direction.
     *
     * @param string $column
     * @param string $direction
     * @return static
     */
    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->builder->orderBy($column, $direction);
        return $this;
    }

    /**
     * Add where conditions to the query.
     *
     * @param array $conditions
     * @return static
     */
    public function where(array $conditions): static
    {
        foreach ($conditions as $col => $val) {
            if (is_array($val) && count($val) === 3) {
                $this->builder->where($val[0], $val[1], $val[2]);
            } else {
                $this->builder->where($col, $val);
            }
        }
        return $this;
    }

    /**
     * Search across multiple columns with a keyword.
     *
     * @param array $columns
     * @param string $keyword
     * @return static
     */
    public function search(array $columns, string $keyword): static
    {
        $this->builder->where(function (Builder $q) use ($columns, $keyword) {
            foreach ($columns as $i => $col) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $q->{$method}($col, 'like', '%' . addcslashes($keyword, '%_\\') . '%');
            }
        });
        return $this;
    }

    /**
     * Conditionally apply a callback to the query.
     *
     * @param bool $condition
     * @param callable $callback
     * @return static
     */
    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            $callback($this, $this->builder);
        }
        return $this;
    }

    /**
     * Forward calls to the underlying builder.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->builder, $name)) {
            $result = $this->builder->{$name}(...$arguments);
            return $result instanceof Builder ? $this : $result;
        }

        throw new \BadMethodCallException(sprintf(
            '%s::%s does not exist.',
            static::class,
            $name
        ));
    }
}