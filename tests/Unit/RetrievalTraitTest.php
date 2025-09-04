<?php

namespace Tests\Unit;

use Awtechs\DataSource\Eloquent\Traits\RetrievalTrait;
use Awtechs\DataSource\Eloquent\Traits\CachingTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\TestCase;
use Mockery;

class RetrievalTraitTest extends TestCase
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

    public function testAllUsesCache()
    {
        $builder = Mockery::mock(Builder::class);
        $collection = new Collection([new TestModel]);
        $builder->shouldReceive('get')->with(['*'])->once()->andReturn($collection);

        $repository = $this->createRepository($builder);
        $repository->cacheResults(true);
        $result = $repository->all();
        $this->assertSame($collection, $result);
        $this->assertNull($repository->builder); // Ensure reset
    }

    public function testPaginateRespectsPerPage()
    {
        $builder = Mockery::mock(Builder::class);
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $builder->shouldReceive('paginate')->with(10, ['*'])->once()->andReturn($paginator);

        $repository = $this->createRepository($builder);
        $result = $repository->paginate(10);
        $this->assertSame($paginator, $result);
    }

    public function testFindUsesFreshQuery()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->with(1, ['*'])->once()->andReturn(new TestModel);

        $repository = $this->createRepository($builder);
        $result = $repository->find(1);
        $this->assertInstanceOf(TestModel::class, $result);
    }

    private function createRepository(Builder $builder)
    {
        return new class($builder) {
            use RetrievalTrait, CachingTrait;
            protected ?string $modelClass = TestModel::class;
            protected Builder $builder;
            protected int $defaultPerPage = 15;
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
?>