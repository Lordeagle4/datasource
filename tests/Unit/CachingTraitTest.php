<?php

namespace Tests\Unit;

use Awtechs\DataSource\Eloquent\Traits\CachingTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;
use Mockery;

class CachingTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCacheKeyGeneration()
    {
        $repository = $this->createRepository();
        $key = $repository->cacheKey('testMethod', ['arg1']);
        $this->assertStringContainsString('repo:TestModel:testMethod:', $key);
        $this->assertStringContainsString($repository->queryHash(), $key);
    }

    public function testRememberUsesCacheWhenEnabled()
    {
        $repository = $this->createRepository();
        $repository->cacheResults(true)->cacheDuration(3600);

        $cache = Mockery::mock('Illuminate\Cache\Repository');
        Cache::shouldReceive('supportsTags')->andReturn(false);
        Cache::shouldReceive('driver')->andReturn($cache);
        $cache->shouldReceive('remember')->once()->andReturn('cached_result');

        $result = $repository->remember('test', ['arg'], fn() => 'result');
        $this->assertEquals('cached_result', $result);
    }

    public function testRememberBypassesCacheWhenDisabled()
    {
        $repository = $this->createRepository();
        $repository->cacheResults(false);

        $result = $repository->remember('test', ['arg'], fn() => 'direct_result');
        $this->assertEquals('direct_result', $result);
    }

    public function testFlushCacheWithTagging()
    {
        $repository = $this->createRepository();
        $repository->cacheResults(true);

        $cache = Mockery::mock('Illuminate\Cache\TaggedCache');
        Cache::shouldReceive('supportsTags')->andReturn(true);
        Cache::shouldReceive('tags')->with('repo:TestModel')->andReturn($cache);
        $cache->shouldReceive('flush')->once();

        $repository->flushCache();
    }

    private function createRepository()
    {
        return new class {
            use CachingTrait;
            protected ?string $modelClass = TestModel::class;
            protected Builder $builder;
            public function __construct()
            {
                $this->builder = Mockery::mock(Builder::class);
                $this->builder->shouldReceive('toSql')->andReturn('SELECT * FROM test');
                $this->builder->shouldReceive('getBindings')->andReturn([]);
            }
            public function queryHash(): string
            {
                return md5($this->builder->toSql() . json_encode($this->builder->getBindings()));
            }
        };
    }
}

class TestModel {}