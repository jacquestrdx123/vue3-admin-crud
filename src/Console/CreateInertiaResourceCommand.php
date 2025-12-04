<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateInertiaResourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:inertia-resource {model : The model class name (e.g., User, Product)} 
                            {--controller : Generate the controller}
                            {--routes : Generate route definitions}
                            {--vue : Generate Vue page files}
                            {--all : Generate controller, routes, and Vue files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new InertiaResource with optional controller, routes, and Vue files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->argument('model');
        $generateController = $this->option('controller') || $this->option('all');
        $generateRoutes = $this->option('routes') || $this->option('all');
        $generateVue = $this->option('vue') || $this->option('all');

        // Validate model exists
        if (!class_exists($model)) {
            $this->error("Model class '{$model}' does not exist.");
            return 1;
        }

        $modelName = class_basename($model);
        $resourceName = $modelName.'Resource';
        $controllerName = $modelName.'Controller';
        $slug = Str::kebab(Str::plural($modelName));

        $this->info("Creating InertiaResource for {$modelName}...");
        $this->newLine();

        // Determine namespaces and paths
        $appNamespace = $this->getAppNamespace();
        $resourceNamespace = $appNamespace.'Support\\Inertia\\Resources';
        $controllerNamespace = $appNamespace.'Http\\Controllers';

        // Create directories if they don't exist
        $resourcePath = app_path('Support/Inertia/Resources');
        $controllerPath = app_path('Http/Controllers');

        if (!File::exists($resourcePath)) {
            File::makeDirectory($resourcePath, 0755, true);
        }

        if ($generateController && !File::exists($controllerPath)) {
            File::makeDirectory($controllerPath, 0755, true);
        }

        // Generate InertiaResource
        $vuePagePath = $generateVue ? "Resources/{$modelName}" : 'Resources';
        $this->generateResource($model, $modelName, $resourceName, $resourceNamespace, $resourcePath, $slug, $vuePagePath);

        // Generate Controller
        if ($generateController) {
            $this->generateController($model, $modelName, $controllerName, $controllerNamespace, $controllerPath, $resourceNamespace, $resourceName, $slug);
        }

        // Generate Routes
        if ($generateRoutes) {
            $this->generateRoutes($modelName, $controllerName, $controllerNamespace, $slug);
        }

        // Generate Vue Files
        if ($generateVue) {
            $this->generateVueFiles($modelName, $slug);
        }

        $this->newLine();
        $this->info('✅ InertiaResource created successfully!');
        $this->newLine();

        if ($generateRoutes) {
            $this->comment('⚠️  Don\'t forget to add the routes to your routes file (web.php or api.php)');
        }

        return 0;
    }

    /**
     * Generate the InertiaResource class
     */
    protected function generateResource(string $model, string $modelName, string $resourceName, string $namespace, string $path, string $slug, string $vuePagePath = 'Resources'): void
    {
        $filePath = "{$path}/{$resourceName}.php";

        if (File::exists($filePath)) {
            if (!$this->confirm("Resource file {$resourceName}.php already exists. Overwrite?", true)) {
                $this->warn("Skipped {$resourceName}.php");
                return;
            }
        }

        $stub = File::get(__DIR__.'/../../stubs/InertiaResource.stub');
        $stub = str_replace('{{ namespace }}', $namespace, $stub);
        $stub = str_replace('{{ resourceName }}', $resourceName, $stub);
        $stub = str_replace('{{ model }}', $model, $stub);
        $stub = str_replace('{{ modelName }}', $modelName, $stub);
        $stub = str_replace('{{ slug }}', $slug, $stub);
        $stub = str_replace('{{ vuePagePath }}', $vuePagePath, $stub);

        // Add default columns for User resource
        if ($modelName === 'User') {
            $columnsSection = "                TextColumn::make('id', 'ID'),\n";
            $columnsSection .= "                TextColumn::make('name', 'Name'),\n";
            $columnsSection .= "                TextColumn::make('email', 'EMAIL'),\n";
            $columnsSection .= "                // Add your columns here";
            
            $stub = preg_replace(
                "/TextColumn::make\('id', 'ID'\),\s*\n\s*\/\/ Add your columns here/",
                $columnsSection,
                $stub
            );
        }

        // Add default columns and form fields for MenuGroup resource
        if ($modelName === 'MenuGroup') {
            // Add imports for additional column and field types
            $stub = str_replace(
                "use InertiaResource\Columns\TextColumn;\nuse InertiaResource\FormFields\TextField;",
                "use InertiaResource\Columns\TextColumn;\nuse InertiaResource\Columns\BooleanColumn;\nuse InertiaResource\Columns\GenericColumn;\nuse InertiaResource\FormFields\TextField;\nuse InertiaResource\FormFields\NumberField;\nuse InertiaResource\FormFields\ToggleField;",
                $stub
            );
            
            // Generate columns
            $columnsSection = "                TextColumn::make('id', 'ID'),\n";
            $columnsSection .= "                TextColumn::make('key', 'Key'),\n";
            $columnsSection .= "                TextColumn::make('label', 'Label'),\n";
            $columnsSection .= "                TextColumn::make('icon', 'Icon'),\n";
            $columnsSection .= "                GenericColumn::make('sort_order', 'Sort Order'),\n";
            $columnsSection .= "                BooleanColumn::make('is_active', 'Active'),\n";
            $columnsSection .= "                // Add your columns here";
            
            $stub = preg_replace(
                "/TextColumn::make\('id', 'ID'\),\s*\n\s*\/\/ Add your columns here/",
                $columnsSection,
                $stub
            );
            
            // Generate form fields
            $formFieldsSection = "            TextField::make('key', 'Key')->required(),\n";
            $formFieldsSection .= "            TextField::make('label', 'Label')->required(),\n";
            $formFieldsSection .= "            TextField::make('icon', 'Icon'),\n";
            $formFieldsSection .= "            NumberField::make('sort_order', 'Sort Order')->default(0),\n";
            $formFieldsSection .= "            ToggleField::make('is_active', 'Active')->default(true),";
            
            $stub = preg_replace(
                "/\/\/ Add your form fields here\s*\n\s*\/\/ TextField::make\('name', 'Name'\)->required\(\),/",
                $formFieldsSection,
                $stub
            );
        }

        // Add default columns and form fields for MenuItem resource
        if ($modelName === 'MenuItem') {
            // Add imports for additional column and field types
            $stub = str_replace(
                "use InertiaResource\Columns\TextColumn;\nuse InertiaResource\FormFields\TextField;",
                "use InertiaResource\Columns\TextColumn;\nuse InertiaResource\Columns\BooleanColumn;\nuse InertiaResource\Columns\GenericColumn;\nuse InertiaResource\FormFields\TextField;\nuse InertiaResource\FormFields\NumberField;\nuse InertiaResource\FormFields\ToggleField;\nuse InertiaResource\FormFields\SelectField;",
                $stub
            );
            
            // Generate columns
            $columnsSection = "                TextColumn::make('id', 'ID'),\n";
            $columnsSection .= "                GenericColumn::make('menu_group_id', 'Menu Group'),\n";
            $columnsSection .= "                GenericColumn::make('parent_id', 'Parent'),\n";
            $columnsSection .= "                TextColumn::make('key', 'Key'),\n";
            $columnsSection .= "                TextColumn::make('label', 'Label'),\n";
            $columnsSection .= "                TextColumn::make('url', 'URL'),\n";
            $columnsSection .= "                TextColumn::make('route', 'Route'),\n";
            $columnsSection .= "                TextColumn::make('icon', 'Icon'),\n";
            $columnsSection .= "                TextColumn::make('permission_name', 'Permission'),\n";
            $columnsSection .= "                GenericColumn::make('sort_order', 'Sort Order'),\n";
            $columnsSection .= "                BooleanColumn::make('is_active', 'Active'),\n";
            $columnsSection .= "                BooleanColumn::make('is_group_header', 'Group Header'),\n";
            $columnsSection .= "                // Add your columns here";
            
            $stub = preg_replace(
                "/TextColumn::make\('id', 'ID'\),\s*\n\s*\/\/ Add your columns here/",
                $columnsSection,
                $stub
            );
            
            // Generate form fields
            $formFieldsSection = "            SelectField::make('menu_group_id', 'Menu Group')\n";
            $formFieldsSection .= "                ->options([])\n";
            $formFieldsSection .= "                ->required(),\n";
            $formFieldsSection .= "            SelectField::make('parent_id', 'Parent')\n";
            $formFieldsSection .= "                ->options([]),\n";
            $formFieldsSection .= "            TextField::make('key', 'Key')->required(),\n";
            $formFieldsSection .= "            TextField::make('label', 'Label')->required(),\n";
            $formFieldsSection .= "            TextField::make('url', 'URL'),\n";
            $formFieldsSection .= "            TextField::make('route', 'Route'),\n";
            $formFieldsSection .= "            TextField::make('icon', 'Icon'),\n";
            $formFieldsSection .= "            TextField::make('permission_name', 'Permission'),\n";
            $formFieldsSection .= "            NumberField::make('sort_order', 'Sort Order')->default(0),\n";
            $formFieldsSection .= "            ToggleField::make('is_active', 'Active')->default(true),\n";
            $formFieldsSection .= "            ToggleField::make('is_group_header', 'Group Header')->default(false),";
            
            $stub = preg_replace(
                "/\/\/ Add your form fields here\s*\n\s*\/\/ TextField::make\('name', 'Name'\)->required\(\),/",
                $formFieldsSection,
                $stub
            );
        }

        File::put($filePath, $stub);
        $this->info("✅ Created {$resourceName}.php");
    }

    /**
     * Generate the Controller class
     */
    protected function generateController(string $model, string $modelName, string $controllerName, string $namespace, string $path, string $resourceNamespace, string $resourceName, string $slug): void
    {
        $filePath = "{$path}/{$controllerName}.php";

        if (File::exists($filePath)) {
            if (!$this->confirm("Controller file {$controllerName}.php already exists. Overwrite?", true)) {
                $this->warn("Skipped {$controllerName}.php");
                return;
            }
        }

        $stub = File::get(__DIR__.'/../../stubs/ResourceController.stub');
        $stub = str_replace('{{ namespace }}', $namespace, $stub);
        $stub = str_replace('{{ controllerName }}', $controllerName, $stub);
        $stub = str_replace('{{ resourceNamespace }}', $resourceNamespace, $stub);
        $stub = str_replace('{{ resourceName }}', $resourceName, $stub);
        $stub = str_replace('{{ model }}', $model, $stub);
        $stub = str_replace('{{ modelName }}', $modelName, $stub);
        $stub = str_replace('{{ routePrefix }}', $slug, $stub);
        $stub = str_replace('{{ routeName }}', "admin.{$slug}", $stub);

        File::put($filePath, $stub);
        $this->info("✅ Created {$controllerName}.php");
    }

    /**
     * Generate route definitions
     */
    protected function generateRoutes(string $modelName, string $controllerName, string $controllerNamespace, string $slug): void
    {
        $routesPath = base_path('routes');
        $adminRoutesFile = "{$routesPath}/admin.php";

        // Check if admin.php exists, if not, warn user
        if (!File::exists($adminRoutesFile)) {
            $this->warn("⚠️  Could not find routes/admin.php file. Please run 'php artisan vue-admin-panel:install' first.");
            $this->displayRoutes($modelName, $controllerName, $controllerNamespace, $slug);
            return;
        }

        $stub = File::get(__DIR__.'/../../stubs/routes.stub');
        $stub = str_replace('{{ routePrefix }}', $slug, $stub);
        $stub = str_replace('{{ controllerNamespace }}', $controllerNamespace, $stub);
        $stub = str_replace('{{ controllerName }}', $controllerName, $stub);
        $stub = str_replace('{{ routeName }}', "admin.{$slug}", $stub);

        $routesContent = File::get($adminRoutesFile);
        
        // Check if routes already exist in admin.php
        if (strpos($routesContent, "Route::prefix('{$slug}')") !== false) {
            $this->warn("⚠️  Routes for '{$slug}' already exist in routes/admin.php. Skipping route generation.");
            $this->displayRoutes($modelName, $controllerName, $controllerNamespace, $slug);
            return;
        }

        // Find the protected admin routes group and add routes inside it
        // Look for the closing of the protected admin routes group (after logout route)
        // The structure is: protected group closes with "    });" then admin prefix closes with "});"
        $insertPosition = strrpos($routesContent, "        })->name('logout');\n");
        
        if ($insertPosition !== false) {
            // Insert after logout route, before closing of protected admin routes group
            // Need to add proper indentation (8 spaces) to match the protected group indentation
            $indentedStub = preg_replace('/^/m', '        ', $stub);
            $beforeLogout = substr($routesContent, 0, $insertPosition + strlen("        })->name('logout');\n"));
            $afterLogout = substr($routesContent, $insertPosition + strlen("        })->name('logout');\n"));
            $routesContent = $beforeLogout . "        \n" . $indentedStub . "\n" . $afterLogout;
        } else {
            // Fallback: try to find the protected middleware group closing before admin prefix closes
            $insertPosition = strrpos($routesContent, "    });\n});\n\n// API routes");
            if ($insertPosition !== false) {
                // Insert before the closing of protected admin routes group
                // Need to add proper indentation (8 spaces)
                $indentedStub = preg_replace('/^/m', '        ', $stub);
                $beforeClose = substr($routesContent, 0, $insertPosition + strlen("    });\n"));
                $afterClose = substr($routesContent, $insertPosition + strlen("    });\n"));
                $routesContent = $beforeClose . "\n" . $indentedStub . "\n" . $afterClose;
            } else {
                // Last fallback: try to find before API routes section (outside admin prefix)
                $insertPosition = strrpos($routesContent, "});\n\n// API routes for column preferences");
                if ($insertPosition !== false) {
                    $beforeApi = substr($routesContent, 0, $insertPosition);
                    $afterApi = substr($routesContent, $insertPosition);
                    $routesContent = $beforeApi . "\n" . $stub . "\n" . $afterApi;
                } else {
                    // Final fallback: append to end of file
                    $routesContent .= "\n\n" . $stub;
                }
            }
        }

        File::put($adminRoutesFile, $routesContent);
        $this->info("✅ Added routes to routes/admin.php");
    }

    /**
     * Display route definitions for manual addition
     */
    protected function displayRoutes(string $modelName, string $controllerName, string $controllerNamespace, string $slug): void
    {
        $this->newLine();
        $this->comment("Add these routes to your routes/admin.php file inside the admin prefix group:");
        $this->newLine();
        $this->line("// {$slug} routes");
        $this->line("Route::prefix('{$slug}')->name('{$slug}.')->middleware([App\\Http\\Middleware\\AuthenticateAdmin::class])->group(function () {");
        $this->line("    Route::get('/', [{$controllerNamespace}\\{$controllerName}::class, 'index'])->name('index');");
        $this->line("    Route::get('/create', [{$controllerNamespace}\\{$controllerName}::class, 'create'])->name('create');");
        $this->line("    Route::post('/', [{$controllerNamespace}\\{$controllerName}::class, 'store'])->name('store');");
        $this->line("    Route::get('/{id}', [{$controllerNamespace}\\{$controllerName}::class, 'show'])->name('show');");
        $this->line("    Route::get('/{id}/edit', [{$controllerNamespace}\\{$controllerName}::class, 'edit'])->name('edit');");
        $this->line("    Route::put('/{id}', [{$controllerNamespace}\\{$controllerName}::class, 'update'])->name('update');");
        $this->line("    Route::delete('/{id}', [{$controllerNamespace}\\{$controllerName}::class, 'destroy'])->name('destroy');");
        $this->line("    Route::post('/bulk-action', [{$controllerNamespace}\\{$controllerName}::class, 'bulkAction'])->name('bulk-action');");
        $this->line("});");
        $this->newLine();
    }

    /**
     * Generate Vue page files
     */
    protected function generateVueFiles(string $modelName, string $slug): void
    {
        $vuePath = resource_path('js/Pages/Resources/'.$modelName);

        if (!File::exists($vuePath)) {
            File::makeDirectory($vuePath, 0755, true);
        }

        $pages = ['Index', 'Create', 'Edit', 'Show'];

        foreach ($pages as $page) {
            $fileName = "{$page}.vue";
            $filePath = "{$vuePath}/{$fileName}";

            if (File::exists($filePath)) {
                if (!$this->confirm("Vue file {$fileName} already exists. Overwrite?", true)) {
                    $this->warn("Skipped {$fileName}");
                    continue;
                }
            }

            $stubPath = __DIR__."/../../stubs/Pages/Resources/{$page}.vue.stub";
            
            if (!File::exists($stubPath)) {
                $this->warn("⚠️  Stub file not found: {$stubPath}");
                continue;
            }

            $stub = File::get($stubPath);
            $stub = str_replace('{{ resourceSlug }}', $slug, $stub);
            $stub = str_replace('{{ title }}', Str::title(Str::singular($slug)), $stub);

            File::put($filePath, $stub);
            $this->info("✅ Created {$fileName}");
        }
    }

    /**
     * Get the application namespace
     */
    protected function getAppNamespace(): string
    {
        $composer = json_decode(File::get(base_path('composer.json')), true);
        
        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath(app_path()) === realpath(base_path($pathChoice))) {
                    return $namespace;
                }
            }
        }

        return 'App\\';
    }
}

