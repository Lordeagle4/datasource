<?php

declare(strict_types=1);

namespace Awtechs\DataSource\Eloquent\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

trait CachingTrait
{
    protected bool $cacheResults = false;

    protected int $cacheDuration = 3600; // seconds

    protected ?int $cacheTtlPerChain = null;

    /**
     * Enable or disable caching for repository operations globally.
     *
     * @param bool $enabled
     * @return static
     */
    public function cacheResults(bool $enabled): static
    {
        $this->cacheResults = $enabled;
        return $this;
    }

    /**
     * Set the default cache duration in seconds.
     *
     * @param int $seconds
     * @return static
     */
    public function cacheDuration(int $seconds): static
    {
        $this->cacheDuration = $seconds;
        return $this;
    }

    /**
     * Enable caching for the current query chain with a specific TTL.
     *
     * @param int $seconds
     * @return static
     */
    public function cacheFor(int $seconds): static
    {
        $this->cacheTtlPerChain = $seconds;
        return $this;
    }

    /**
     * Generate a cache key for the given method and arguments.
     *
     * @param string $method
     * @param mixed $args
     * @return string
     */
    protected function cacheKey(string $method, $args = []): string
    {
        $name = class_basename($this->modelClass);
        $argString = md5(serialize($args));
        $queryHash = $this->queryHash();
        return "repo:{$name}:{$method}:{$argString}:{$queryHash}";
    }

    /**
     * Generate a hash of the current query builder state.
     *
     * @return string
     */
    protected function queryHash(): string
    {
        return md5($this->builder->toSql() . json_encode($this->builder->getBindings()));
    }

    /**
     * Wrap a callback with caching if enabled.
     *
     * @param string $method
     * @param mixed $args
     * @param Closure $callback
     * @return mixed
     */
    protected function remember(string $method, $args, Closure $callback): mixed
    {
        $ttl = $this->cacheTtlPerChain ?? ($this->cacheResults ? $this->cacheDuration : 0);
        if ($ttl <= 0) {
            return $callback();
        }

        $tag = 'repo:' . class_basename($this->modelClass);
        $key = $this->cacheKey($method, $args);

        $cache = Cache::supportsTags() ? Cache::tags($tag) : Cache::driver();

        return $cache->remember($key, $ttl, $callback);
    }

    /**
     * Flush the cache for this model's repository.
     *
     * @return void
     */
    protected function flushCache(): void
    {
        if (!$this->cacheResults) {
            return;
        }

        $tag = 'repo:' . class_basename($this->modelClass);
        if (Cache::supportsTags()) {
            Cache::tags($tag)->flush();
        }
        // If no tag support, cannot flush wildcard; consider tracking keys if needed
    }
}