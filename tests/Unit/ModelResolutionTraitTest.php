<?php

namespace Tests\Unit;

use Awtechs\DataSource\Eloquent\Traits\ModelResolutionTrait;
use Awtechs\Datasource\Exceptions\ModelNotResolvableException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ModelResolutionTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Mock Laravel's app() helper for model resolution
        App::shouldReceive('getNamespace')->andReturn('App\\');
    }

    public function testResolveModelClassWithExplicitModel()
    {
        $repository = new class {
            use ModelResolutionTrait;
            public static function model(): ?string
            {
                return TestModel::class;
            }
        };

        $result = $repository->resolveModelClass(get_class($repository));
        $this->assertEquals(TestModel::class, $result);
    }

    public function testResolveModelClassWithAutoResolution()
    {
        // Mock class_exists for App\Models\TestModel
        $this->mockClassExists('App\Models\TestModel', true);

        $repository = new class {
            use ModelResolutionTrait;
            protected ?string $modelClass = null;
        };

        $result = $repository->resolveModelClass('App\Repositories\TestRepository');
        $this->assertEquals('App\Models\TestModel', $result);
    }

    public function testResolveModelClassThrowsExceptionWhenDisabled()
    {
        Config::shouldReceive('get')->with('datasource.auto_resolve_models', true)->andReturn(false);

        $repository = new class {
            use ModelResolutionTrait;
            protected ?string $modelClass = null;
        };

        $this->expectException(ModelNotResolvableException::class);
        $repository->resolveModelClass('App\Repositories\TestRepository');
    }

    public function testExtractTopLevelImports()
    {
        $repository = new class {
            use ModelResolutionTrait;
        };

        // Mock ReflectionClass and file contents
        $reflection = $this->createMock(ReflectionClass::class);
        $reflection->method('getFileName')->willReturn(__DIR__ . '/stubs/TestRepository.php');
        ReflectionClass::setStaticPropertyValue(get_class($repository), 'importsCache', []);

        $result = $repository->extractTopLevelImports(get_class($repository));
        $this->assertArrayHasKey('TestModel', $result);
        $this->assertEquals('App\Models\TestModel', $result['TestModel']);
    }

    private function mockClassExists(string $class, bool $exists): void
    {
        // Simulate class_exists behavior
        $GLOBALS['class_exists_mock'] = function ($checkedClass) use ($class, $exists) {
            if ($checkedClass === $class) {
                return $exists;
            }
            return false;
        };
        // Override class_exists globally for this test
        function class_exists($class, $autoload = true) {
            return $GLOBALS['class_exists_mock']($class);
        }
    }
}

class TestModel extends Model {
    //protected $table = 'test_models';
}
?>