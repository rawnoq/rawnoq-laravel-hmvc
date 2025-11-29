# Laravel HMVC Package

A professional, feature-rich Hierarchical Model-View-Controller (HMVC) package for Laravel applications. This package provides a complete modular architecture solution with automatic route loading, view registration, migration management, and comprehensive Artisan command support.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Module Management](#module-management)
- [Artisan Commands](#artisan-commands)
- [Module Structure](#module-structure)
- [Configuration](#configuration)
- [Advanced Usage](#advanced-usage)
- [Examples](#examples)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### Core Features
- ✅ **Complete HMVC Architecture** - Organize your application into independent, reusable modules
- ✅ **Automatic Route Loading** - Routes are automatically discovered and loaded from modules
- ✅ **View Registration** - Module views are automatically registered with Laravel
- ✅ **Translation Support** - Automatic language file loading from modules
- ✅ **Migration Management** - Module-specific migrations with dedicated commands
- ✅ **Service Provider Support** - Each module can have its own service providers
- ✅ **Configuration Management** - Module-specific configuration files
- ✅ **Module Status Management** - Enable/disable modules dynamically

### Artisan Commands
- ✅ **Make Commands** - Full support for creating components within modules
- ✅ **Module Management Commands** - Create, list, enable, disable modules
- ✅ **Migration & Seeding** - Module-specific database operations
- ✅ **Case-Insensitive** - Module names work regardless of case

### Supported Components
All Laravel `make:` commands support the `--module` option:
- Controllers, Models, Requests, Factories, Seeders, Migrations, Policies
- Middleware, Commands, Events, Listeners, Observers
- Resources, Tests, Notifications, Mail, Jobs, Exceptions
- Rules, Casts, Channels, Components, Enums, Scopes
- Views, Classes, Interfaces, Traits, DTOs, Config, Providers, Job Middleware

## 📦 Requirements

- PHP 8.2 or higher
- Laravel 11.0+ or Laravel 12.0+

## 🚀 Installation

### Step 1: Install via Composer

```bash
composer require rawnoq/laravel-hmvc
```

The package will automatically register PSR-4 autoloading for your modules. **No manual configuration needed!**

### Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=hmvc-config
```

This will publish the configuration file to `config/hmvc.php`.

### Step 3: Publish Stubs (Optional)

```bash
php artisan vendor:publish --tag=hmvc-stubs
```

This will publish module stubs to `stubs/hmvc/module/` for customization.

### ⚠️ Note on Autoloading

The package automatically registers PSR-4 autoloading for the `Modules` namespace. You **do not need** to manually add it to your `composer.json`. The autoloading is registered programmatically and works on both **Linux** and **Windows** systems.

## 🎯 Quick Start

### Create Your First Module

```bash
php artisan module:make Blog
```

This command will create a complete module structure:

```
modules/
└── Blog/
    ├── Config/
    │   └── config.php
    ├── Database/
    │   ├── Factories/
    │   ├── Migrations/
    │   └── Seeders/
    │       └── DatabaseSeeder.php
    ├── Http/
    │   ├── Controllers/
    │   │   └── BlogController.php
    │   ├── Middleware/
    │   ├── Requests/
    │   └── Resources/
    ├── Providers/
    │   └── ServiceProvider.php
    ├── Resources/
    │   ├── lang/
    │   └── views/
    ├── Routes/
    │   ├── web.php
    │   └── api.php
    └── Models/
```

### Create Components Within Modules

```bash
# Create a controller in a module
php artisan make:controller PostController --module=Blog

# Create a model with migration and factory
php artisan make:model Post --module=Blog --migration --factory

# Create a request
php artisan make:request StorePostRequest --module=Blog

# Create middleware
php artisan make:middleware CheckAuth --module=Blog
```

## 📚 Module Management

### List All Modules

```bash
php artisan module:list
```

Output:
```
+--------+---------+--------+-------------+-----------+------------+-------+
| Module | Status  | Routes | Controllers | Providers | Migrations | Views |
+--------+---------+--------+-------------+-----------+------------+-------+
| Blog   | Enabled | ✓      | ✓           | ✓         | ✓          | ✓     |
| User   | Enabled | ✓      | ✓           | ✓         | ✗          | ✓     |
+--------+---------+--------+-------------+-----------+------------+-------+
```

### Enable/Disable Modules

```bash
# Enable a module
php artisan module:enable Blog

# Disable a module
php artisan module:disable Blog
```

### Run Module Migrations

```bash
# Run migrations for a specific module
php artisan module:migrate Blog

# Run migrations with seeding
php artisan module:migrate Blog --seed
```

### Run Module Seeders

```bash
php artisan module:seed Blog
```

## 🛠️ Artisan Commands

### Module Management Commands

| Command | Description |
|---------|-------------|
| `module:make {name}` | Create a new HMVC module scaffold |
| `module:list` | List all registered HMVC modules |
| `module:enable {name}` | Enable a disabled HMVC module |
| `module:disable {name}` | Disable an HMVC module |
| `module:migrate {name}` | Run database migrations for a specific module |
| `module:seed {name}` | Run the database seeder for a specific module |

### Module Options

The `module:make` command supports several options:

```bash
# Create a module with force (overwrite if exists)
php artisan module:make Blog --force

# Create a plain module (no scaffold)
php artisan module:make Blog --plain

# Create a module with API routing scaffold
php artisan module:make Blog --api
```

### Make Commands with Module Support

All Laravel `make:` commands support the `--module` option:

#### Basic Components
```bash
php artisan make:controller PostController --module=Blog
php artisan make:model Post --module=Blog
php artisan make:request StorePostRequest --module=Blog
php artisan make:middleware CheckAuth --module=Blog
php artisan make:policy PostPolicy --module=Blog
```

#### Database Components
```bash
php artisan make:migration create_posts_table --module=Blog
php artisan make:factory PostFactory --module=Blog
php artisan make:seeder PostSeeder --module=Blog
```

#### Event System
```bash
php artisan make:event PostCreated --module=Blog
php artisan make:listener SendPostNotification --module=Blog
php artisan make:observer PostObserver --module=Blog
```

#### Jobs & Queues
```bash
php artisan make:job ProcessPost --module=Blog
php artisan make:job-middleware RateLimitMiddleware --module=Blog
```

#### API & Resources
```bash
php artisan make:resource PostResource --module=Blog
php artisan make:test PostTest --module=Blog
```

#### Notifications & Mail
```bash
php artisan make:notification PostPublished --module=Blog
php artisan make:mail PostMail --module=Blog
```

#### Validation & Rules
```bash
php artisan make:rule CustomRule --module=Blog
php artisan make:cast JsonCast --module=Blog
```

#### Broadcasting
```bash
php artisan make:channel PostChannel --module=Blog
```

#### View Components
```bash
php artisan make:component PostCard --module=Blog
php artisan make:view post.show --module=Blog
```

#### Other Components
```bash
php artisan make:command ProcessCommand --module=Blog
php artisan make:enum PostStatus --module=Blog
php artisan make:scope PublishedScope --module=Blog
php artisan make:class PostService --module=Blog
php artisan make:interface PostRepositoryInterface --module=Blog
php artisan make:trait HasSlug --module=Blog
php artisan make:dto PostDto --module=Blog
php artisan make:config post --module=Blog
php artisan make:provider PostServiceProvider --module=Blog
php artisan make:exception PostNotFoundException --module=Blog
```

**Note:** All commands work normally without `--module` option (standard Laravel behavior).

## 📁 Module Structure

A typical module structure looks like this (matching Laravel's standard structure):

```
modules/
└── Blog/
    ├── App/
    │   ├── Http/
    │   │   ├── Controllers/         # Controllers
    │   │   ├── Middleware/          # HTTP middleware
    │   │   ├── Requests/            # Form requests
    │   │   └── Resources/           # API resources
    │   ├── Models/                  # Eloquent models
    │   ├── Providers/
    │   │   └── ServiceProvider.php  # Module service provider
    │   ├── Policies/                # Authorization policies
    │   ├── Events/                  # Event classes
    │   ├── Listeners/               # Event listeners
    │   ├── Observers/               # Model observers
    │   ├── Notifications/           # Notification classes
    │   ├── Mail/                    # Mail classes
    │   ├── Jobs/                    # Job classes
    │   ├── Exceptions/              # Exception classes
    │   ├── Rules/                   # Validation rules
    │   ├── Casts/                   # Custom casts
    │   ├── Broadcasting/            # Broadcasting channels
    │   ├── View/
    │   │   └── Components/          # View components
    │   ├── Enums/                   # Enums
    │   ├── Console/
    │   │   └── Commands/            # Artisan commands
    │   ├── Interfaces/              # Interfaces
    │   ├── Traits/                  # Traits
    │   └── DTOs/                    # Data Transfer Objects
    ├── Database/
    │   ├── Factories/               # Model factories
    │   ├── Migrations/              # Database migrations
    │   └── Seeders/                 # Database seeders
    ├── Resources/
    │   ├── Lang/                    # Translation files
    │   └── Views/                   # Blade views
    ├── Routes/
    │   ├── web.php                  # Web routes
    │   └── api.php                  # API routes
    ├── Config/
    │   └── config.php               # Module configuration
    └── Tests/                       # Test classes
```

## ⚙️ Configuration

The configuration file is located at `config/hmvc.php`:

```php
return [
    // Module namespace
    'namespace' => 'Modules',

    // Modules directory path
    'modules_path' => base_path('modules'),

    // Status file location
    'status_file' => storage_path('app/hmvc/modules.php'),

    // Directory structure for modules
    'directories' => [
        'controllers' => ['App/Http/Controllers'],
        'models' => ['App/Models'],
        'requests' => ['App/Http/Requests'],
        // ... and more
    ],

    // Route configuration
    'routes' => [
        [
            'name' => 'web',
            'path' => 'routes/web.php',
            'middleware' => ['web'],
            'prefix' => null,
            'enabled' => true,
        ],
        [
            'name' => 'api',
            'path' => 'routes/api.php',
            'middleware' => ['api'],
            'prefix' => 'api',
            'enabled' => true,
        ],
    ],
];
```

### Customizing Module Directories

You can customize the directory structure in `config/hmvc.php`:

```php
'directories' => [
    'controllers' => ['App/Http/Controllers', 'Controllers'],
    'models' => ['App/Models', 'Entities'],
    // Add custom directories
],
```

## 🎓 Advanced Usage

### Module Service Providers

Each module can have its own service provider located at `Providers/ServiceProvider.php`:

```php
<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register module services
    }

    public function boot(): void
    {
        // Boot module services
    }
}
```

#### Registering Class Aliases

The package provides two ways to register class aliases:

**1. Using the Helper Function (Recommended):**

**Single alias:**
```php
<?php

namespace Modules\Authentication\App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        register_class_alias(
            'Modules\Authentication\App\Models\BaseUser',
            config('authentication_dependencies.models.user')
        );
    }
}
```

**Multiple aliases at once (snake_case):**
```php
<?php

namespace Modules\Authentication\App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        register_class_aliases([
            'Modules\Authentication\App\Models\BaseUser' => config('authentication_dependencies.models.user'),
            'Modules\Authentication\App\Http\Resources\UserResource' => config('authentication_dependencies.resources.user'),
        ]);
    }
}
```

**Multiple aliases at once (camelCase):**
```php
<?php

namespace Modules\Authentication\App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        registerClassAliases([
            'Modules\Authentication\App\Models\BaseUser' => config('authentication_dependencies.models.user'),
            'Modules\Authentication\App\Http\Resources\UserResource' => config('authentication_dependencies.resources.user'),
        ]);
    }
}
```

**Note:** Both `register_class_aliases()` (snake_case) and `registerClassAliases()` (camelCase) are available. Use whichever naming convention you prefer.

**2. Using the Trait:**

```php
<?php

namespace Modules\Authentication\App\Providers;

use Illuminate\Support\ServiceProvider;
use Rawnoq\HMVC\Support\RegistersClassAliases;

class ServiceProvider extends ServiceProvider
{
    use RegistersClassAliases;

    public function register(): void
    {
        $this->registerClassAlias(
            'Modules\Authentication\App\Models\BaseUser',
            config('authentication_dependencies.models.user')
        );
    }
}
```

**Benefits:**
- Allows modules to reference classes by a consistent name
- Actual implementation can be swapped via configuration
- Prevents duplicate alias registration
- Type-safe and maintainable

### Module Routes

#### Web Routes (`routes/web.php`)

```php
<?php

use Modules\Blog\App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{post}', [BlogController::class, 'show']);
```

#### API Routes (`routes/api.php`)

```php
<?php

use Modules\Blog\App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::apiResource('posts', BlogController::class);
```

### Module Views

Views are automatically registered with the module name as namespace:

```php
// In controller
return view('blog::posts.index', ['posts' => $posts]);

// In Blade
@include('blog::partials.header')
```

### Module Translations

Translation files are automatically loaded:

```php
// In code
trans('blog::messages.welcome');

// In Blade
{{ __('blog::messages.welcome') }}
```

### Module Migrations

Migrations are automatically discovered and can be run with:

```bash
php artisan module:migrate Blog
```

Or run all migrations (including modules):

```bash
php artisan migrate
```

### Module Configuration

Access module configuration:

```php
config('blog.some_key');
```

## 📝 Examples

### Example 1: Complete Blog Module

```bash
# Create module
php artisan module:make Blog

# Create models
php artisan make:model Post --module=Blog --migration --factory
php artisan make:model Category --module=Blog --migration --factory

# Create controllers
php artisan make:controller PostController --module=Blog --resource
php artisan make:controller CategoryController --module=Blog --resource

# Create requests
php artisan make:request StorePostRequest --module=Blog
php artisan make:request UpdatePostRequest --module=Blog

# Create policies
php artisan make:policy PostPolicy --module=Blog --model=Post

# Create seeders
php artisan make:seeder PostSeeder --module=Blog
```

### Example 2: API Module with Events

```bash
# Create module
php artisan module:make Api --plain

# Create components
php artisan make:model User --module=Api --migration
php artisan make:event UserCreated --module=Api
php artisan make:listener SendWelcomeEmail --module=Api --event=UserCreated
php artisan make:job ProcessUserRegistration --module=Api
php artisan make:notification WelcomeNotification --module=Api
```

### Example 3: E-commerce Module

```bash
# Create module
php artisan module:make Ecommerce

# Create models
php artisan make:model Product --module=Ecommerce --migration --factory
php artisan make:model Order --module=Ecommerce --migration --factory
php artisan make:model Cart --module=Ecommerce --migration

# Create services
php artisan make:class ProductService --module=Ecommerce
php artisan make:class OrderService --module=Ecommerce

# Create repositories
php artisan make:interface ProductRepositoryInterface --module=Ecommerce
php artisan make:class ProductRepository --module=Ecommerce

# Create DTOs
php artisan make:dto CreateProductDto --module=Ecommerce
php artisan make:dto UpdateOrderDto --module=Ecommerce
```

## 🔧 Troubleshooting

### Module Not Found

If you get "Module does not exist" error:

1. Check module name (case-insensitive)
2. Verify module exists in `modules/` directory
3. Run `php artisan module:list` to see all modules

### Routes Not Loading

1. Ensure module is enabled: `php artisan module:enable ModuleName`
2. Check route files exist: `modules/ModuleName/routes/web.php` or `api.php`
3. Clear route cache: `php artisan route:clear`

### Views Not Found

1. Verify view files exist in `modules/ModuleName/Resources/views/`
2. Use correct namespace: `module-name::view.name`
3. Clear view cache: `php artisan view:clear`

## 🧪 Testing

```bash
composer test
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for more details.

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 📦 Data Transfer Objects (DTOs)

The package includes a powerful DTO system with a base class for type-safe data transfer:

### Creating DTOs

```bash
# Create a DTO in app
php artisan make:dto UserDto

# Create a DTO in a module
php artisan make:dto PostDto --module=Blog
```

### DTO Structure

All DTOs extend `Rawnoq\HMVC\DTOs\BaseDto` which provides:

- `fromArray(array $data): static` - Create DTO from array
- `fromRequest(Request $request): static` - Create DTO from validated request
- `toArray(): array` - Convert DTO to array

### Example DTO

```php
<?php

namespace Modules\Blog\App\DTOs;

use Rawnoq\HMVC\DTOs\BaseDto;

final readonly class PostDto extends BaseDto
{
    public function __construct(
        public string $title,
        public string $content,
        public ?string $slug = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            title: $data['title'],
            content: $data['content'],
            slug: $data['slug'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
        ];
    }
}
```

### Using DTOs

```php
// In a controller
use Modules\Blog\App\DTOs\PostDto;

public function store(StorePostRequest $request)
{
    $dto = PostDto::fromRequest($request);
    
    // Use DTO with type safety
    $post = Post::create($dto->toArray());
    
    return response()->json($post);
}
```

## 🙏 Credits

- **Author:** Rawnoq
- **Package:** rawnoq/laravel-hmvc
- **Version:** 1.0.3

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [HMVC Architecture](https://en.wikipedia.org/wiki/Hierarchical_model%E2%80%93view%E2%80%93controller)

---

**Made with ❤️ for the Laravel community**

