# Vue Admin Panel

A Filament-like resource system for Inertia.js applications. This package provides a complete CRUD interface system with tables, forms, filters, actions, and more. Includes Vue 3 components with Tailwind CSS 4 support.

## Requirements

- PHP >= 8.1
- Laravel >= 12.0
- Node.js >= 18.0.0
- npm >= 9.0.0
- Inertia.js (automatically installed with this package)
- Vue 3.x (automatically installed with this package)
- Tailwind CSS 4 (automatically installed with this package)

## Installation

### Quick Install

Simply run:

```bash
composer require jacquestrdx123/vue3-admin-crud
```

This will automatically:

- ✅ Install all PHP/Laravel dependencies via Composer
- ✅ Update your `package.json` with Vue 3 and Tailwind CSS 4 dependencies
- ✅ Set up the package structure

### Complete Setup

After the Composer installation, run:

```bash
# Run the installer (publishes assets, updates package.json, and runs npm install)
php artisan vue-admin-panel:install
```

The installer will automatically:

- ✅ Merge npm dependencies into your `package.json` (including `laravel-vite-plugin`)
- ✅ Run `npm install` to install all dependencies
- ✅ Publish all package assets (components, config, migrations)
- ✅ Set up Tailwind CSS configuration

> **Note**: If you encounter "Cannot find package 'laravel'" errors, make sure `npm install` completed successfully. The `laravel-vite-plugin` package is required for Vite to work with Laravel.

Or manually publish assets:

```bash
# Publish all assets (config, migrations, Vue components, Tailwind config)
php artisan vendor:publish --tag=inertia-resource

# Or publish individually:
php artisan vendor:publish --tag=inertia-resource-config      # Configuration only
php artisan vendor:publish --tag=inertia-resource-migrations  # Migrations only
php artisan vendor:publish --tag=inertia-resource-components   # Vue components only
php artisan vendor:publish --tag=inertia-resource-tailwind    # Tailwind config only
```

### Vite Configuration

Update your `vite.config.js` to include Tailwind CSS 4 and Vue support:

```javascript
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      "@": "/resources/js",
    },
  },
});
```

## Updating the Package

When you update the package to a new version, you may need to republish assets to get the latest Vue components, configuration files, or other assets.

### Republish Assets After Update

After updating the package via Composer:

```bash
composer update jacquestrdx123/vue3-admin-crud
```

Republish assets using one of these methods:

#### Option 1: Use the Publish Command (Recommended)

```bash
# Republish all assets (will skip existing files)
php artisan vue-admin-panel:publish

# Force republish all assets (overwrites existing files)
php artisan vue-admin-panel:publish --force
```

#### Option 2: Use Laravel's Vendor Publish Command

```bash
# Republish all assets (will skip existing files)
php artisan vendor:publish --tag=inertia-resource

# Force republish all assets (overwrites existing files)
php artisan vendor:publish --tag=inertia-resource --force

# Republish specific asset groups
php artisan vendor:publish --tag=inertia-resource-components --force  # Vue components only
php artisan vendor:publish --tag=inertia-resource-config --force      # Config only
php artisan vendor:publish --tag=inertia-resource-tailwind --force    # Tailwind config only
```

### What Gets Republished?

- **Vue Components**: Latest versions of table, form, and UI components
- **Configuration**: Updated config file (if you use `--force`)
- **Tailwind Config**: Updated Tailwind configuration (if you use `--force`)
- **CSS Assets**: Updated CSS files

> **Note**: Without `--force`, Laravel will skip files that already exist. Use `--force` to overwrite existing files with the latest versions from the package.

### CSS Setup

Ensure your main CSS file (`resources/css/app.css`) includes Tailwind:

```css
@import "tailwindcss";
```

If you're using the package's CSS file, import it in your main CSS:

```css
@import "tailwindcss";
@import "./vue-admin-panel.css";
```

### Import Vue Components

After publishing, components will be available at `resources/js/vendor/inertia-resource/`. Import them in your Vue files:

```javascript
// In your page components
import BaseDataTable from "@/vendor/inertia-resource/Components/Table/BaseDataTable.vue";
import FormBuilder from "@/vendor/inertia-resource/Components/Form/FormBuilder.vue";
import TextField from "@/vendor/inertia-resource/Components/Form/TextField.vue";
import SelectField from "@/vendor/inertia-resource/Components/Form/SelectField.vue";
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=inertia-resource-config
```

Configure your models and paths in `config/inertia-resource.php`:

```php
return [
    'user_model' => \App\Models\User::class,
    'customer_model' => null, // Optional
    'column_preference_model' => \InertiaResource\Models\UserColumnPreference::class, // Optional
    'resource_paths' => [
        app_path('Support/Inertia/Resources'),
    ],
    'route_prefix' => 'vue',
];
```

## Optional: Column Preferences

If you want to use the column preference feature, publish and run the migration:

```bash
php artisan vendor:publish --tag=inertia-resource-migrations
php artisan migrate
```

Then configure the model in your config:

```php
'column_preference_model' => \InertiaResource\Models\UserColumnPreference::class,
```

## Usage

### Creating a Resource

Create a resource class extending `InertiaResource\Inertia\InertiaResource`:

```php
<?php

namespace App\Support\Inertia\Resources;

use InertiaResource\Inertia\InertiaResource;
use InertiaResource\Columns\TextColumn;
use InertiaResource\Columns\MoneyColumn;
use InertiaResource\FormFields\TextField;
use InertiaResource\Filters\SelectFilter;

class InvoiceResource extends InertiaResource
{
    protected static ?string $model = \App\Models\Invoice::class;
    protected static ?string $slug = 'invoices';
    protected static ?string $navigationGroup = 'Billing';
    protected static ?int $navigationSort = 10;

    public static function table(): array
    {
        return [
            'columns' => [
                TextColumn::make('invoice_number', 'Invoice #'),
                MoneyColumn::make('total', 'Total'),
                TextColumn::make('status', 'Status'),
            ],
            'filters' => [
                SelectFilter::make('status', 'Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                    ]),
            ],
        ];
    }

    public static function form(): array
    {
        return [
            TextField::make('invoice_number', 'Invoice Number')
                ->required(),
            TextField::make('total', 'Total')
                ->type('number')
                ->required(),
        ];
    }
}
```

### Creating a Controller

Create a controller extending `InertiaResource\Http\Controllers\BaseResourceController`:

```php
<?php

namespace App\Http\Controllers\Inertia;

use App\Support\Inertia\Resources\InvoiceResource;
use App\Models\Invoice;
use InertiaResource\Http\Controllers\BaseResourceController;

class InvoiceController extends BaseResourceController
{
    protected function getResourceClass(): string
    {
        return InvoiceResource::class;
    }

    protected function getModel(): string
    {
        return Invoice::class;
    }

    protected function getIndexRoute(): string
    {
        return 'vue.invoices.index';
    }
}
```

### Routes

Add routes for your resource:

```php
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/create', [InvoiceController::class, 'create'])->name('create');
    Route::post('/', [InvoiceController::class, 'store'])->name('store');
    Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('edit');
    Route::put('/{id}', [InvoiceController::class, 'update'])->name('update');
    Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [InvoiceController::class, 'bulkAction'])->name('bulk-action');
});
```

## Customization

### Custom Search Logic

Implement the `SearchQueryBuilder` interface:

```php
use InertiaResource\Contracts\SearchQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CustomSearchQueryBuilder implements SearchQueryBuilder
{
    public function apply(Builder $query, Request $request, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
    }
}
```

Then bind it in a service provider:

```php
$this->app->singleton(SearchQueryBuilder::class, CustomSearchQueryBuilder::class);
```

### Custom Column Preferences

Implement the `ColumnPreferenceRepository` interface:

```php
use InertiaResource\Contracts\ColumnPreferenceRepository;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomPreferenceRepository implements ColumnPreferenceRepository
{
    public function getPreferencesForResource(Authenticatable $user, string $resourceSlug): ?array
    {
        // Your implementation
    }

    public function savePreferencesForResource(Authenticatable $user, string $resourceSlug, array $preferences): void
    {
        // Your implementation
    }
}
```

## Testing

Run the test suite:

```bash
composer test
```

Or with coverage:

```bash
composer test-coverage
```

## Development

### Running Tests

The package uses Pest PHP for testing. Tests are located in the `tests/` directory.

```bash
cd vue-admin-panel
composer install
composer test
```

### Package Structure

```
vue-admin-panel/
├── src/                    # PHP source files
│   ├── Actions/            # Action classes
│   ├── Columns/           # Column classes
│   ├── Contracts/         # Interfaces
│   ├── Filters/           # Filter classes
│   ├── FormFields/        # Form field classes
│   ├── Http/              # Controllers
│   ├── Inertia/           # Core resource classes
│   ├── Models/            # Eloquent models
│   └── Vue/               # Vue components (legacy)
├── resources/
│   └── js/                # Vue components, pages, composables
│       ├── Components/    # Vue components
│       ├── Pages/         # Example page templates
│       └── Composables/   # Vue composables
├── config/                 # Configuration files
├── database/               # Migration stubs
└── tests/                  # Test files
```

## Documentation

For detailed documentation, see the [PACKAGE_SUMMARY.md](PACKAGE_SUMMARY.md) file.

## License

MIT

# vue3-admin-crud
