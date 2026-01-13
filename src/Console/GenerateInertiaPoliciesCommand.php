<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InertiaResource\Inertia\InertiaResource;
use ReflectionClass;

class GenerateInertiaPoliciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:inertia-policies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Policy files for all InertiaResources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Discovering InertiaResources...');
        $this->newLine();

        $resourcesPath = app_path('Support/Inertia/Resources');

        if (! File::exists($resourcesPath)) {
            $this->error("Resources directory not found: {$resourcesPath}");
            $this->info('No InertiaResources found. Create resources first using: php artisan make:inertia-resource');

            return 1;
        }

        $resourceFiles = $this->discoverResources($resourcesPath);

        if (empty($resourceFiles)) {
            $this->warn('No InertiaResource files found.');
            $this->info('Create resources first using: php artisan make:inertia-resource');

            return 0;
        }

        $this->info('Found '.count($resourceFiles).' InertiaResource file(s).');
        $this->newLine();

        $created = [];
        $skipped = [];
        $errors = [];

        foreach ($resourceFiles as $file) {
            try {
                $result = $this->processResourceFile($file);
                if ($result === 'created') {
                    $created[] = $file->getFilename();
                } elseif ($result === 'skipped') {
                    $skipped[] = $file->getFilename();
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getFilename(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Display summary
        $this->newLine();
        if (! empty($created)) {
            $this->info('✅ Created '.count($created).' policy file(s):');
            foreach ($created as $filename) {
                $this->line("  - {$filename}");
            }
        }

        if (! empty($skipped)) {
            $this->warn('⚠️  Skipped '.count($skipped).' policy file(s):');
            foreach ($skipped as $filename) {
                $this->line("  - {$filename}");
            }
        }

        if (! empty($errors)) {
            $this->error('❌ Errors encountered:');
            foreach ($errors as $error) {
                $this->line("  - {$error['file']}: {$error['error']}");
            }
        }

        if (empty($created) && empty($skipped) && empty($errors)) {
            $this->info('No policies were generated.');
        }

        $this->newLine();

        return 0;
    }

    /**
     * Discover all InertiaResource files
     */
    protected function discoverResources(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $files = File::allFiles($path);

        return array_filter($files, function ($file) {
            return $file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Resource.php');
        });
    }

    /**
     * Process a single resource file
     */
    protected function processResourceFile($file): ?string
    {
        $filePath = $file->getPathname();
        $resourcesBasePath = app_path('Support/Inertia/Resources');
        $relativePath = str_replace($resourcesBasePath, '', $filePath);
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        // Extract class name from file path
        // Example: User/UserResource.php -> App\Support\Inertia\Resources\User\UserResource
        // Example: Admin/User/UserResource.php -> App\Support\Inertia\Resources\Admin\User\UserResource
        $appNamespace = $this->getAppNamespace();
        $directory = dirname($relativePath);
        $className = pathinfo($relativePath, PATHINFO_FILENAME);

        if ($directory === '.' || $directory === '') {
            $fullClassName = "{$appNamespace}Support\\Inertia\\Resources\\{$className}";
        } else {
            $namespacePath = str_replace('/', '\\', $directory);
            $fullClassName = "{$appNamespace}Support\\Inertia\\Resources\\{$namespacePath}\\{$className}";
        }

        // Check if class exists
        if (! class_exists($fullClassName)) {
            $this->warn("⚠️  Class '{$fullClassName}' not found. Skipping {$file->getFilename()}.");

            return 'skipped';
        }

        // Use reflection to get the model class
        try {
            $reflection = new ReflectionClass($fullClassName);

            // Check if it extends InertiaResource
            if (! $reflection->isSubclassOf(InertiaResource::class)) {
                $this->warn("⚠️  Class '{$fullClassName}' does not extend InertiaResource. Skipping {$file->getFilename()}.");

                return 'skipped';
            }

            // Get the model class from static property
            $model = $reflection->getStaticPropertyValue('model');

            if (empty($model)) {
                // Try using getModel() method if available
                if ($reflection->hasMethod('getModel')) {
                    $model = $fullClassName::getModel();
                }
            }

            if (empty($model)) {
                $this->warn("⚠️  Could not determine model class for '{$fullClassName}'. Skipping {$file->getFilename()}.");

                return 'skipped';
            }

            // Validate model class exists
            if (! class_exists($model)) {
                $this->warn("⚠️  Model class '{$model}' does not exist. Skipping {$file->getFilename()}.");

                return 'skipped';
            }

            // Get slug from resource
            $slug = $reflection->getStaticPropertyValue('slug');
            if (empty($slug)) {
                // Try using getSlug() method if available
                if ($reflection->hasMethod('getSlug')) {
                    $slug = $fullClassName::getSlug();
                }
            }

            // If slug is still empty, derive from model name
            if (empty($slug)) {
                $modelName = class_basename($model);
                $slug = Str::kebab(Str::plural($modelName));
            }

            // Extract namespace path from model
            $modelNamespacePath = $this->extractModelNamespacePath($model);
            $modelName = class_basename($model);

            // Determine policy namespace and path
            $policyNamespacePath = $this->extractParentNamespacePath($modelNamespacePath);
            $policyNamespacePathForPhp = $policyNamespacePath ? '\\'.str_replace('/', '\\', $policyNamespacePath) : '';
            $policyNamespace = $appNamespace.'Policies'.$policyNamespacePathForPhp;
            $policyPath = app_path('Policies/'.($policyNamespacePath ?: ''));

            // Generate policy
            return $this->generatePolicy($model, $modelName, $policyNamespace, $policyPath, $slug) ? 'created' : 'skipped';
        } catch (\ReflectionException $e) {
            $this->error("❌ Reflection error for '{$fullClassName}': {$e->getMessage()}");

            return 'skipped';
        }
    }

    /**
     * Generate the Policy class
     */
    protected function generatePolicy(string $model, string $modelName, string $namespace, string $path, string $slug): bool
    {
        $policyName = $modelName.'Policy';
        $filePath = "{$path}/{$policyName}.php";

        if (File::exists($filePath)) {
            if (! $this->confirm("Policy file {$policyName}.php already exists. Overwrite?", true)) {
                $this->warn("Skipped {$policyName}.php");

                return false;
            }
        }

        // Create directory if it doesn't exist
        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Convert slug from kebab-case to snake_case for permission prefix
        // Example: rainfall-data -> rainfall_data
        $permissionPrefix = str_replace('-', '_', $slug);

        // Get camelCase version of model name for variable names
        $modelVariable = Str::camel($modelName);

        $stub = File::get(__DIR__.'/../../stubs/Policy.stub');
        $stub = str_replace('{{ namespace }}', $namespace, $stub);
        $stub = str_replace('{{ policyName }}', $policyName, $stub);
        $stub = str_replace('{{ model }}', $model, $stub);
        $stub = str_replace('{{ modelName }}', $modelName, $stub);
        $stub = str_replace('{{ modelVariable }}', $modelVariable, $stub);
        $stub = str_replace('{{ permissionPrefix }}', $permissionPrefix, $stub);

        File::put($filePath, $stub);
        $this->info("✅ Created {$policyName}.php");

        return true;
    }

    /**
     * Extract namespace path from model class directly from the input string
     * Example: App\Models\Users\User -> Users/User
     * Example: App\Models\User -> User
     * Example: App\Models\Admin\User -> Admin/User
     */
    protected function extractModelNamespacePath(string $model): string
    {
        // Find the position of "Models\" in the string
        $modelsPos = strpos($model, 'Models\\');

        if ($modelsPos !== false) {
            // Get everything after "Models\"
            $afterModels = substr($model, $modelsPos + 7); // 7 = length of "Models\"

            // Convert backslashes to forward slashes for file paths
            return str_replace('\\', '/', $afterModels);
        }

        // Fallback: just use the class basename
        return class_basename($model);
    }

    /**
     * Extract parent namespace path (excluding the model name)
     * Example: Admin/User -> Admin
     * Example: User -> (empty string)
     * Example: Users/User -> Users
     */
    protected function extractParentNamespacePath(string $modelNamespacePath): string
    {
        // Split by forward slash
        $parts = explode('/', $modelNamespacePath);

        // If there's only one part (just the model name), return empty string
        if (count($parts) <= 1) {
            return '';
        }

        // Remove the last part (model name) and join the rest
        array_pop($parts);

        return implode('/', $parts);
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
