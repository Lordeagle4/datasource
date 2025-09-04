<?php

namespace Tests\Unit;

use Awtechs\DataSource\Eloquent\Traits\QueryBuilderTrait;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;
use Mockery;

class QueryBuilderTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testWithAddsRelations()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->with(['relation'])->once()->andReturnSelf();

        $repository = $this->createRepository($builder);
        $result = $repository->with(['relation']);
        $this->assertSame($repository, $result);
    }

    public function testWhereAddsConditions()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')->with('column', 'value')->once()->andReturnSelf();
        $builder->shouldReceive('where')->with('col', '=', 'val')->once()->andReturnSelf();

        $repository = $this->createRepository($builder);
        $repository->where(['column' => 'value', ['col', '=', 'val']]);
    }

    public function testSearchAddsLikeConditions()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')->once()->andReturnUsing(function ($callback) use ($builder) {
            $q = Mockery::mock(Builder::class);
            $q->shouldReceive('where')->with('col1', 'like', '%test%')->once()->andReturnSelf();
            $q->shouldReceive('orWhere')->with('col2', 'like', '%test%')->once()->andReturnSelf();
            $callback($q);
            return $builder;
        });

        $repository = $this->createRepository($builder);
        $repository->search(['col1', 'col2'], 'test');
    }

    public function testDynamicMethodCall()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('whereIn')->with(['ids'], [1, 2, 3])->once()->andReturnSelf();

        $repository = $this->createRepository($builder);
        $result = $repository->whereIn(['ids'], [1, 2, 3]);
        $this->assertSame($repository, $result);
    }

    private function createRepository(Builder $builder)
    {
        return new class($builder) {
            use QueryBuilderTrait;
            protected Builder $builder;
            public function __construct(Builder $builder)
            {
                $this->builder = $builder;
            }
        };
    }
}
?>