# Awtechs DataSource Package

A flexible and extensible Laravel repository pattern implementation for Eloquent models, providing a clean interface for data access with built-in caching and query-building utilities.

## Features

- **Repository Pattern**: Abstracts Eloquent model interactions for cleaner, testable code.
- **Model Resolution**: Automatically or explicitly resolves Eloquent models based on repository class names or configurations.
- **Fluent Query Building**: Chainable methods for `where`, `with`, `search`, and more.
- **Caching Support**: Optional caching of query results with configurable TTL, using Laravel's cache drivers.
- **CRUD Operations**: Simplified methods for creating, updating, deleting, and restoring models.
- **Soft Deletes**: Handles soft-deleted models with `restore` and `forceDelete` methods.
- **Modular Design**: Organized into traits for maintainability and reusability.
- **Comprehensive Tests**: Unit tests covering model resolution, caching, queries, and mutations.

## Requirements

- PHP >= 8.1
- Laravel >= 9.x
- Composer
- PHPUnit and Mockery for running tests

## Installation

1. Install the package via Composer:
   ```bash
   composer require awtechs/datasource
   ```

2. (Optional) Publish the configuration file:
   ```bash
   php artisan vendor:publish --provider="Awtechs\DataSource\DataSourceServiceProvider"
   ```
   This creates a `config/datasource.php` file for customizing settings.

## Configuration

The configuration file (`config/datasource.php`) allows you to customize the package behavior:

```php
return [
    // Enable/disable automatic model resolution based on repository naming conventions
    'auto_resolve_models' => env('DATASOURCE_AUTO_RESOLVE_MODELS', true),

    // Enable/disable caching of query results
    'cache_results' => env('DATASOURCE_CACHE_RESULTS', false),

    // Default cache duration in seconds
    'cache_duration' => env('DATASOURCE_CACHE_DURATION', 3600),

    // Default items per page for pagination
    'default_per_page' => env('DATASOURCE_DEFAULT_PER_PAGE', 15),
];
```

You can override these settings in your `.env` file, e.g.:
```
DATASOURCE_AUTO_RESOLVE_MODELS=true
DATASOURCE_CACHE_RESULTS=true
DATASOURCE_CACHE_DURATION=7200
DATASOURCE_DEFAULT_PER_PAGE=20
```

## Usage

### Creating a Repository

1. Create a repository class extending `Awtechs\DataSource\Eloquent\BaseRepository`:
   ```php
   namespace App\Repositories;

   use Awtechs\DataSource\Eloquent\BaseRepository;
   use App\Models\User;

   class UserRepository extends BaseRepository
   {
       public static function model(): ?string
       {
           return User::class;
       }
   }
   ```

2. Alternatively, set the `$modelClass` property directly:
   ```php
   namespace App\Repositories;

   use Awtechs\DataSource\Eloquent\BaseRepository;
   use App\Models\User;

   class UserRepository extends BaseRepository
   {
       protected ?string $modelClass = User::class;
   }
   ```

3. If `auto_resolve_models` is enabled, the repository can infer the model (e.g., `UserRepository` maps to `App\Models\User`).

### Dependency Injection

Inject the repository into your controllers or services using Laravel's IoC container:

```php
namespace App\Http\Controllers;

use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function index()
    {
        $users = $this->users->all();
        return view('users.index', compact('users'));
    }
}
```

### Example Operations

#### Retrieve All Records
```php
$users = $this->users->all(['id', 'name']);
```

#### Paginate Results
```php
$users = $this->users->paginate(10, ['id', 'name']);
```

#### Query with Conditions
```php
$users = $this->users
    ->with(['posts'])
    ->where(['role' => 'admin'])
    ->orderBy('created_at', 'desc')
    ->all();
```

#### Search Across Columns
```php
$users = $this->users->search(['name', 'email'], 'john')->all();
```

#### Cache Results
```php
$users = $this->users->cacheFor(3600)->all(); // Cache for 1 hour
```

#### Create a Record
```php
$user = $this->users->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
]);
```

#### Update a Record
```php
$this->users->update(1, ['name' => 'Jane Doe']);
```

#### Delete a Record
```php
$this->users->delete(1); // Soft delete if model uses SoftDeletes
$this->users->forceDelete(1); // Permanent delete
```

#### Restore a Soft-Deleted Record
```php
$this->users->restore(1);
```

#### Transaction
```php
$this->users->transaction(function ($repo) {
    $repo->create(['name' => 'Test User', 'email' => 'test@example.com']);
    $repo->update(1, ['name' => 'Updated User']);
});
```

## Testing

The package includes comprehensive unit tests for all functionality. To run the tests:

1. Ensure PHPUnit and Mockery are installed:
   ```bash
   composer require --dev phpunit/phpunit mockery/mockery
   ```

2. Copy the test files from the package's `tests/Unit` directory to your project's `tests/Unit` directory.

3. Run the tests:
   ```bash
   vendor/bin/phpunit
   ```

The tests cover:
- Model resolution (explicit and auto-resolution)
- Caching behavior (key generation, cache enable/disable, flushing)
- Query building (with, where, search, dynamic methods)
- Retrieval operations (all, paginate, find, findOrFail, firstWhere)
- Mutation operations (create, update, delete, restore)

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/my-feature`).
3. Commit your changes (`git commit -am 'Add my feature'`).
4. Push to the branch (`git push origin feature/my-feature`).
5. Create a Pull Request.

Please ensure your code follows PSR-12 standards and includes tests for new functionality.

## License

This package is open-sourced under the [MIT License](LICENSE).

## Support

For issues, questions, or suggestions, please open an issue on the [GitHub repository](https://github.com/awtechs/datasource).