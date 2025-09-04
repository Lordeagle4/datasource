<?php

namespace Tests\Unit;

use Awtechs\DataSource\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase;
use Mockery;

class BaseRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        App::shouldReceive('make')->andReturnUsing(function ($class) {
            return new $class;
        });
        Config::shouldReceive('get')->with('datasource.cache_results', false)->andReturn(false);
        Config::shouldReceive('get')->with('datasource.cache_duration', 3600)->andReturn(3600);
        Config::shouldReceive('get')->with('datasource.default_per_page', 15)->andReturn(15);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testConstructorResolvesModel()
    {
        $builder = Mockery::mock(Builder::class);
        $model = Mockery::mock(TestModel::class);
        $model->shouldReceive('newQuery')->andReturn($builder);
        App::shouldReceive('make')->with(TestModel::class)->andReturn($model);

        $repository = new class extends BaseRepository {
            public static function model(): ?string
            {
                return TestModel::class;
            }
        };

        $this->assertInstanceOf(Builder::class, $repository->query());
    }

    public function testChainingMethods()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->with(['relation'])->andReturnSelf();
        $builder->shouldReceive('where')->with('column', 'value')->andReturnSelf();
        $builder->shouldReceive('get')->with(['*'])->andReturn(new \Illuminate\Database\Eloquent\Collection);

        $model = Mockery::mock(TestModel::class);
        $model->shouldReceive('newQuery')->andReturn($builder);
        App::shouldReceive('make')->with(TestModel::class)->andReturn($model);

        $repository = new class extends BaseRepository {
            public static function model(): ?string
            {
                return TestModel::class;
            }
        };

        $result = $repository->with(['relation'])->where(['column' => 'value'])->all();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }
}

class TestModel extends Model {}
?>