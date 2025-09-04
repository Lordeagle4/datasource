<?php

namespace Tests\Unit;

use Awtechs\DataSource\Eloquent\Traits\MutationTrait;
use Awtechs\DataSource\Eloquent\Traits\RetrievalTrait;
use Awtechs\DataSource\Eloquent\Traits\CachingTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;
use Mockery;

class MutationTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        App::shouldReceive('make')->andReturnUsing(function ($class) {
            return new $class;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateFlushesCache()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(TestModel::class);
        $model->shouldReceive('create')->with(['name' => 'test'])->once()->andReturnSelf();
        App::shouldReceive('make')->with(TestModel::class)->andReturn($model);

        $repository = $this->createRepository($builder);
        $repository->cacheResults(true);
        $cache = Mockery::mock('Illuminate\Cache\TaggedCache');
        Cache::shouldReceive('supportsTags')->andReturn(true);
        Cache::shouldReceive('tags')->with('repo:TestModel')->andReturn($cache);
        $cache->shouldReceive('flush')->once();

        $result = $repository->create(['name' => 'test']);
        $this->assertInstanceOf(TestModel::class, $result);
    }

    public function testRestoreWithSoftDeletes()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('withTrashed')->once()->andReturnSelf();
        $builder->shouldReceive('findOrFail')->with(1)->once()->andReturn($model = Mockery::mock(TestModelWithSoftDeletes::class));
        $model->shouldReceive('restore')->once()->andReturn(true);

        $repository = $this->createRepository($builder);
        $result = $repository->restore(1);
        $this->assertTrue($result);
    }

    private function createRepository(Builder $builder)
    {
        return new class($builder) {
            use MutationTrait, RetrievalTrait, CachingTrait;
            protected ?string $modelClass = TestModel::class;
            protected Builder $builder;
            public function __construct(Builder $builder)
            {
                $this->builder = $builder;
            }
            public function queryHash(): string
            {
                return md5($this->builder->toSql() . json_encode($this->builder->getBindings()));
            }
        };
    }
}

class TestModel extends Model {}
class TestModelWithSoftDeletes extends Model {
    use SoftDeletes;
}
?>