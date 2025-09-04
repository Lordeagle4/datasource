<?php

declare(strict_types=1);

namespace Awtechs\DataSource\Eloquent\Traits;

use Awtechs\Datasource\Exceptions\ModelNotResolvableException;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Illuminate\Support\Str;

trait ModelResolutionTrait
{
    /** @var array<string, array<string, class-string>> */
    protected static array $importsCache = [];

    /**
     * Resolve the Eloquent model class for this repository.
     *
     * @param string $repositoryClass
     * @return class-string<Model>
     * @throws ModelNotResolvableException
     */
    protected function resolveModelClass(string $repositoryClass): string
    {
        $autoResolve = config('datasource.auto_resolve_models', true);

        // 1) If child declared static::model(), respect it
        if (method_exists(static::class, 'model')) {
            $declared = static::model();
            if (is_string($declared) && class_exists($declared) && is_subclass_of($declared, Model::class)) {
                return $declared;
            }
        }

        if (!$autoResolve) {
            throw ModelNotResolvableException::for($repositoryClass, 'Auto-resolution is disabled; must define static::model() or set $modelClass.');
        }

        // 2) Guess from repo class name: FooRepository|FooRepo -> Foo
        $short = class_basename($repositoryClass);
        $base = preg_replace('/Repository$/', '', $short);
        if ($base === $short) {
            $base = preg_replace('/Repo$/', '', $short);
        }
        $base = (string) $base;
        $candidateNames = array_values(array_unique([
            $base,
            Str::singular($base), // e.g., PostsRepository -> Post
        ]));

        // 3) Try top-level imports in this file (use statements)
        $imports = $this->extractTopLevelImports($repositoryClass);
        foreach ($candidateNames as $name) {
            if (isset($imports[$name]) && class_exists($imports[$name]) && is_subclass_of($imports[$name], Model::class)) {
                return $imports[$name];
            }
        }

        // 4) Try App\Models\{Name} using the app namespace
        $appNs = app()->getNamespace(); // e.g., "App\"
        foreach ($candidateNames as $name) {
            $fqcn = $appNs . 'Models\\' . $name;
            if (class_exists($fqcn) && is_subclass_of($fqcn, Model::class)) {
                return $fqcn;
            }
        }

        // 5) Give up -> throw a helpful error
        throw ModelNotResolvableException::for($repositoryClass, sprintf(
            "Tried names: %s; Checked imports: %s",
            implode(', ', $candidateNames),
            implode(', ', array_keys($imports))
        ));
    }

    /**
     * Parse top-level `use ...;` statements before the class definition.
     * Returns ['AliasOrClassShortName' => 'Fully\\Qualified\\Name'].
     *
     * Supports: simple uses and grouped uses with optional `as Alias`.
     *
     * @param string $class
     * @return array<string, class-string>
     */
    protected function extractTopLevelImports(string $class): array
    {
        if (isset(static::$importsCache[$class])) {
            return static::$importsCache[$class];
        }

        $ref = new ReflectionClass($class);
        $file = $ref->getFileName();
        if (!$file || !is_readable($file)) {
            return [];
        }

        $src = file_get_contents($file);
        if ($src === false) {
            return [];
        }

        // Only look before the first "class " to avoid trait "use" inside classes
        $header = Str::before($src, 'class ');
        $imports = [];

        // Normalize multi-line group uses
        $lines = preg_split('/\R/u', $header);
        if (!$lines) {
            return [];
        }

        $buffer = '';
        foreach ($lines as $line) {
            $trim = trim($line);
            if (str_starts_with($trim, 'use ')) {
                $buffer = $trim;
                // accumulate until we hit a semicolon
                if (!str_ends_with($trim, ';')) {
                    $buffer .= ' ';
                    continue;
                }
            } elseif ($buffer !== '' && !str_ends_with($buffer, ';')) {
                $buffer .= $trim . ' ';
                if (!str_ends_with($trim, ';')) {
                    continue;
                }
            } else {
                $buffer = '';
                continue;
            }

            if (str_ends_with($buffer, ';')) {
                // Process the full `use ...;`
                $useStmt = rtrim(substr($buffer, 4), " ;\t\n\r\0\x0B");
                $buffer = '';

                // Handle grouped uses: use Foo\Bar\{Baz, Qux as Alias};
                if (preg_match('/^([^{}]+)\{(.+)\}$/', $useStmt, $m)) {
                    $prefix = trim($m[1], "\\ \t");
                    $group = $m[2];
                    foreach (explode(',', $group) as $part) {
                        $part = trim($part);
                        $asAlias = null;
                        if (stripos($part, ' as ') !== false) {
                            [$right, $asAlias] = array_map('trim', preg_split('/\s+as\s+/i', $part));
                        } else {
                            $right = $part;
                        }
                        $fqcn = trim($prefix . '\\' . trim($right, '\\ '), '\\ ');
                        $short = $asAlias ?: class_basename($fqcn);
                        $imports[$short] = $fqcn;
                    }
                } else {
                    // Simple use: use Foo\Bar\Baz as Alias;
                    $asAlias = null;
                    if (stripos($useStmt, ' as ') !== false) {
                        [$fqcn, $asAlias] = array_map('trim', preg_split('/\s+as\s+/i', $useStmt));
                    } else {
                        $fqcn = trim($useStmt);
                    }
                    $fqcn = trim($fqcn, '\\ ');
                    $short = $asAlias ?: class_basename($fqcn);
                    $imports[$short] = $fqcn;
                }
            }
        }

        static::$importsCache[$class] = $imports;
        return $imports;
    }
}