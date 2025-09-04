<?php

declare(strict_types=1);

namespace Awtechs\DataSource\Eloquent;

use Awtechs\DataSource\Contracts\RepositoryInterface;
use Awtechs\DataSource\Eloquent\Traits\CachingTrait;
use Awtechs\DataSource\Eloquent\Traits\ModelResolutionTrait;
use Awtechs\DataSource\Eloquent\Traits\MutationTrait;
use Awtechs\DataSource\Eloquent\Traits\QueryBuilderTrait;
use Awtechs\DataSource\Eloquent\Traits\RetrievalTrait;
use Awtechs\Datasource\Exceptions\ModelNotResolvableException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class BaseRepository implements RepositoryInterface
{
    use ModelResolutionTrait;
    use CachingTrait;
    use QueryBuilderTrait;
    use RetrievalTrait;
    use MutationTrait;

    /** @var class-string<Model>|null */
    protected ?string $modelClass = null;

    protected int $defaultPerPage = 15;

    /**
     * Optional override in child repositories to specify the model class.
     *
     * @return string|null
     */
    public static function model(): ?string
    {
        return null; // children may override
    }

    /**
     * Constructor.
     *
     * @throws ModelNotResolvableException
     */
    public function __construct()
    {
        $this->cacheResults = config('datasource.cache_results', false);
        $this->cacheDuration = config('datasource.cache_duration', 3600);
        $this->defaultPerPage = config('datasource.default_per_page', 15);

        $this->modelClass = $this->resolveModelClass(static::class);
        /** @var Model $model */
        $model = App::make($this->modelClass);
        $this->builder = $model->newQuery();
    }
}
?>