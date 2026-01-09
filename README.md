# Vue Inertia Resources

A Filament-like resource system for Inertia.js applications. This package provides a complete CRUD interface system with tables, forms, filters, actions, and more. Includes Vue 3 components with Tailwind CSS 4 support.

## Requirements

- PHP >= 8.1
- Laravel >= 12.0
- Node.js >= 18.0.0
- npm >= 9.0.0
- Inertia.js (automatically installed with this package)
- Vue 3.x (automatically installed with this package)
- Tailwind CSS 4 (automatically installed with this package)
- Ziggy (automatically installed with this package via Composer)

## Installation

### Quick Install

Simply run:

```bash
composer require jacquestrdx123/vue-inertia-resources
```

This will automatically:

- ✅ Install all PHP/Laravel dependencies via Composer (including Ziggy for route helpers)
- ✅ Update your `package.json` with Vue 3 and Tailwind CSS 4 dependencies
- ✅ Set up the package structure

### Complete Setup

After the Composer installation, run:

```bash
# Run the installer (publishes assets, updates package.json, and runs npm install)
php artisan vue-inertia-resources:install
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
import laravel, { refreshPaths } from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      refresh: refreshPaths,
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
      "@": path.resolve(__dirname, "resources/js"),
      "ziggy-js": path.resolve(
        __dirname,
        "vendor/tightenco/ziggy/dist/vue.es.js"
      ),
    },
  },
});
```

### Ziggy Route Helper Setup

This package includes Ziggy for Laravel route helpers in Vue. **Ziggy is automatically installed via Composer** as a dependency when you install this package - no additional npm package is required.

The `ziggy-js` alias in Vite is just a path alias pointing to the vendor directory (`vendor/tightenco/ziggy/dist/vue.es.js`), not an npm package.

The setup is automatically configured, but if you see `@routes` literally rendering on your page, follow these troubleshooting steps:

1. **Update your Blade template** - Ensure `resources/views/app.blade.php` uses `@routes()` with parentheses:

   ```blade
   <!-- Scripts -->
   @routes()
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   @inertiaHead
   ```

2. **Clear Blade view cache**:

   ```bash
   php artisan view:clear
   ```

3. **Verify Ziggy is installed**:

   ```bash
   composer show tightenco/ziggy
   ```

4. **Check your Vite configuration** - Ensure `vite.config.js` includes the Ziggy alias:

   ```javascript
   resolve: {
     alias: {
       "@": path.resolve(__dirname, "resources/js"),
       "ziggy-js": path.resolve(__dirname, "vendor/tightenco/ziggy/dist/vue.es.js"),
     },
   },
   ```

5. **Check your JavaScript entry point** - Ensure `resources/js/app.js` includes ZiggyVue:

   ```javascript
   import { ZiggyVue } from "ziggy-js";

   createInertiaApp({
     setup({ el, App, props, plugin }) {
       return createApp({ render: () => h(App, props) })
         .use(plugin)
         .use(ZiggyVue) // ← This must be present
         .mount(el);
     },
   });
   ```

6. **If you see "ziggy-js could not be resolved" error in Vite:**

   - Verify Ziggy is installed via Composer: `composer show tightenco/ziggy`
   - Verify the file exists: `ls -la vendor/tightenco/ziggy/dist/vue.es.js`
   - Make sure `vendor/tightenco/ziggy` exists - if not, run `composer install`
   - Ensure your `vite.config.js` includes the `ziggy-js` alias
   - Ensure your `app.js` uses the alias: `import { ZiggyVue } from "ziggy-js"`
   - Restart your Vite dev server after updating the config

## Updating the Package

When you update the package to a new version, you may need to republish assets to get the latest Vue components, configuration files, or other assets.

### Republish Assets After Update

After updating the package via Composer:

```bash
composer update jacquestrdx123/vue-inertia-resources
```

Republish assets using one of these methods:

#### Option 1: Use the Publish Command (Recommended)

```bash
# Republish all assets (will skip existing files)
php artisan vue-inertia-resources:publish

# Force republish all assets (overwrites existing files)
php artisan vue-inertia-resources:publish --force
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
@import "./vue-inertia-resources.css";
```

### Import Vue Components

After publishing, components will be available at `resources/js/vendor/inertia-resource/`. Import them in your Vue files:

```javascript
// In your page components
import BaseDataTable from "@/vendor/inertia-resource/Components/Table/BaseDataTable.vue";
import FormBuilder from "@/vendor/inertia-resource/Components/Form/FormBuilder.vue";
import TextField from "@/vendor/inertia-resource/Components/Form/TextField.vue";
import SelectField from "@/vendor/inertia-resource/Components/Form/SelectField.vue";
import CsvImport from "@/vendor/inertia-resource/Components/Form/CsvImport.vue";
```

## Components

### CSV Import Component

The `CsvImport` component provides a complete CSV import solution with file upload, column mapping, data preview, and example file downloads.

#### Features

- **File Upload**: Drag & drop or click to upload CSV files
- **Column Mapping**: Automatically detect CSV columns and map them to your data fields
- **Data Preview**: Preview parsed data before importing
- **Example Downloads**: Generate example CSV files with correct format
- **Validation**: File type and size validation
- **Auto-mapping**: Intelligent column name matching

#### Basic Usage

```vue
<template>
  <CsvImport
    title="Import Customers"
    description="Upload a CSV file with customer data"
    :columns="columns"
    @import="handleImport"
    @error="handleError"
  />
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import CsvImport from '@/Components/Form/CsvImport.vue'

const columns = [
  {
    key: 'name',
    label: 'Full Name',
    required: true,
    type: 'text'
  },
  {
    key: 'email',
    label: 'Email Address',
    required: true,
    type: 'email'
  },
  {
    key: 'phone',
    label: 'Phone Number',
    required: false,
    type: 'text'
  },
  {
    key: 'birth_date',
    label: 'Birth Date',
    required: false,
    type: 'date',
    example: '1990-01-15'
  }
]

const handleImport = (data) => {
  // data.file - The uploaded File object
  // data.data - Array of mapped data objects
  // data.originalRows - Original CSV rows before mapping
  // data.mapping - Column mapping object
  
  router.post('/admin/customers/import', {
    data: data.data
  }, {
    onSuccess: () => {
      // Handle success
    },
    onError: (errors) => {
      // Handle errors
    }
  })
}

const handleError = (error) => {
  console.error('Import error:', error)
}
</script>
```

#### Column Configuration

Each column in the `columns` array can have the following properties:

- `key` (required) - Field identifier used in the mapped data
- `label` (optional) - Display label (defaults to `key`)
- `description` (optional) - Help text shown below the label
- `required` (optional) - Mark column as required (default: `false`)
- `type` (optional) - Type hint for example generation: `'email'`, `'date'`, `'number'`, `'boolean'`, `'text'`
- `example` (optional) - Custom example value for the CSV template

#### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | `'CSV Import'` | Component title |
| `description` | String | `null` | Component description |
| `columns` | Array | `[]` | Column definitions for mapping |
| `required` | Boolean | `false` | Mark file upload as required |
| `uploadLabel` | String | `'CSV File'` | File upload label |
| `maxSize` | Number | `10240` | Maximum file size in KB (default: 10MB) |
| `importLabel` | String | `'Import'` | Import button label |
| `cancelLabel` | String | `'Cancel'` | Cancel button label |
| `showActions` | Boolean | `true` | Show action buttons |
| `showCancel` | Boolean | `true` | Show cancel button |
| `exampleFileName` | String | `'example.csv'` | Example file download name |
| `errorMessages` | String/Array | `[]` | Error messages to display |

All `FormContainer` props are also supported (padding, shadow, background, rounded, border, maxWidth, class).

#### Events

- `@import` - Emitted when import button is clicked. Receives object with:
  - `file` - The uploaded File object
  - `data` - Array of mapped data objects
  - `originalRows` - Original CSV rows before mapping
  - `mapping` - Column mapping object
- `@cancel` - Emitted when cancel button is clicked
- `@file-selected` - Emitted when a file is selected and parsed
- `@error` - Emitted when an error occurs

#### Example with Backend Integration

```vue
<template>
  <CsvImport
    title="Import Products"
    :columns="productColumns"
    @import="handleImport"
  />
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import CsvImport from '@/Components/Form/CsvImport.vue'

const productColumns = [
  { key: 'sku', label: 'SKU', required: true },
  { key: 'name', label: 'Product Name', required: true },
  { key: 'price', label: 'Price', required: true, type: 'number' },
  { key: 'stock', label: 'Stock Quantity', type: 'number' }
]

const handleImport = async (importData) => {
  const formData = new FormData()
  formData.append('file', importData.file)
  formData.append('data', JSON.stringify(importData.data))
  formData.append('mapping', JSON.stringify(importData.mapping))
  
  router.post('/admin/products/import', formData, {
    forceFormData: true,
    onSuccess: () => {
      // Show success message
    },
    onError: (errors) => {
      // Handle validation errors
    }
  })
}
</script>
```

#### Backend Controller Example

```php
public function import(Request $request)
{
    $request->validate([
        'data' => 'required|array',
        'data.*.sku' => 'required|string',
        'data.*.name' => 'required|string',
        'data.*.price' => 'required|numeric',
    ]);

    $imported = 0;
    $errors = [];

    foreach ($request->input('data') as $index => $row) {
        try {
            Product::create([
                'sku' => $row['sku'],
                'name' => $row['name'],
                'price' => $row['price'],
                'stock' => $row['stock'] ?? 0,
            ]);
            $imported++;
        } catch (\Exception $e) {
            $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
        }
    }

    return redirect()->back()->with([
        'success' => "Successfully imported {$imported} products.",
        'errors' => $errors
    ]);
}
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

### Quick Resource Generation

You can use the `make:inertia-resource` Artisan command to quickly generate a complete InertiaResource setup:

```bash
# Generate InertiaResource only
php artisan make:inertia-resource User

# Generate InertiaResource + Controller
php artisan make:inertia-resource User --controller

# Generate InertiaResource + Routes
php artisan make:inertia-resource User --routes

# Generate InertiaResource + Vue pages
php artisan make:inertia-resource User --vue

# Generate everything (Resource, Controller, Routes, and Vue pages)
php artisan make:inertia-resource User --all
```

**Command Options:**

- `--controller` - Generate the controller class
- `--routes` - Generate route definitions
- `--vue` - Generate Vue page files (Index, Create, Edit, Show)
- `--all` - Generate controller, routes, and Vue files

**What Gets Generated:**

- **InertiaResource**: `app/Support/Inertia/Resources/{Model}Resource.php`
- **Controller**: `app/Http/Controllers/{Model}Controller.php` (if `--controller` or `--all`)
- **Routes**: Added to `routes/web.php` (if `--routes` or `--all`)
- **Vue Pages**: `resources/js/Pages/Resources/{Model}/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` (if `--vue` or `--all`)

**Example:**

```bash
php artisan make:inertia-resource Product --all
```

This will create:

- `app/Support/Inertia/Resources/ProductResource.php`
- `app/Http/Controllers/ProductController.php`
- Routes in `routes/web.php` under `/admin/products` prefix
- Vue pages in `resources/js/Pages/Resources/Product/`

### Creating a Resource Manually

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
        return 'admin.invoices.index';
    }
}
```

### Routes

Add routes for your resource (wrapped in `/admin` prefix):

```php
Route::prefix('admin')->name('admin.')->group(function () {
    Route::prefix('invoices')->name('invoices.')->middleware(['web'])->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [InvoiceController::class, 'bulkAction'])->name('bulk-action');
    });
});
```

**Route Names:**

- `admin.invoices.index`
- `admin.invoices.create`
- `admin.invoices.store`
- `admin.invoices.show`
- `admin.invoices.edit`
- `admin.invoices.update`
- `admin.invoices.destroy`
- `admin.invoices.bulk-action`

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
cd vue-inertia-resources
composer install
composer test
```

### Package Structure

```
vue-inertia-resources/
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

# vue-inertia-resources
