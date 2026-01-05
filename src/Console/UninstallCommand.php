<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-inertia-resources:uninstall 
                            {--force : Skip confirmation prompts}
                            {--keep-migrations : Keep database migrations}
                            {--keep-models : Keep created models}
                            {--keep-routes : Keep created routes}
                            {--keep-config : Keep config file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall Vue Inertia Resources and roll back all changes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('⚠️  WARNING: This will remove Vue Inertia Resources files and configurations!');
        $this->newLine();

        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to uninstall Vue Inertia Resources?', false)) {
                $this->info('Uninstall cancelled.');

                return 0;
            }
        }

        $this->info('🗑️  Uninstalling Vue Inertia Resources...');
        $this->newLine();

        // 1. Remove published assets
        $this->info('📁 Removing published assets...');
        $this->removePublishedAssets();
        $this->newLine();

        // 2. Remove created files
        $this->info('📄 Removing created files...');
        $this->removeCreatedFiles();
        $this->newLine();

        // 3. Remove routes
        if (! $this->option('keep-routes')) {
            $this->info('🛣️  Removing routes...');
            $this->removeRoutes();
            $this->newLine();
        } else {
            $this->comment('⏭️  Skipping routes removal (--keep-routes)');
            $this->newLine();
        }

        // 4. Remove middleware
        $this->info('🔐 Removing middleware...');
        $this->removeMiddleware();
        $this->newLine();

        // 5. Remove models
        if (! $this->option('keep-models')) {
            $this->info('📦 Removing models...');
            $this->removeModels();
            $this->newLine();
        } else {
            $this->comment('⏭️  Skipping models removal (--keep-models)');
            $this->newLine();
        }

        // 6. Remove migrations
        if (! $this->option('keep-migrations')) {
            $this->info('🔄 Removing migrations...');
            $this->removeMigrations();
            $this->newLine();
        } else {
            $this->comment('⏭️  Skipping migrations removal (--keep-migrations)');
            $this->newLine();
        }

        // 7. Remove resources
        $this->info('📋 Removing resources...');
        $this->removeResources();
        $this->newLine();

        // 8. Remove seeders
        $this->info('🌱 Removing seeders...');
        $this->removeSeeders();
        $this->newLine();

        // 9. Ask about npm dependencies
        if (! $this->option('force')) {
            if ($this->confirm('Do you want to remove npm dependencies added by this package?', false)) {
                $this->info('📦 Removing npm dependencies...');
                $this->removeNpmDependencies();
                $this->newLine();
            }
        }

        // 10. Ask about package.json restoration
        if (! $this->option('force')) {
            if ($this->confirm('Do you want to restore package.json to its original state?', false)) {
                $this->info('📝 Restoring package.json...');
                $this->restorePackageJson();
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info('✅ Vue Inertia Resources uninstalled successfully!');
        $this->newLine();

        $this->comment('Note: You may need to manually:');
        $this->comment('  - Remove any custom code that depends on this package');
        $this->comment('  - Update your routes if you kept them');
        $this->comment('  - Clean up any database tables if you kept migrations');

        return 0;
    }

    /**
     * Remove published assets
     */
    protected function removePublishedAssets(): void
    {
        $assets = [
            // Config
            config_path('inertia-resource.php'),

            // Vendor components
            resource_path('js/vendor/inertia-resource'),

            // CSS
            resource_path('css/vue-inertia-resources.css'),

            // Tailwind config (only if it matches package version)
            base_path('tailwind.config.js'),

            // Vite config (only if it matches package version - be careful!)
            // We'll skip this as it might have user customizations
        ];

        $removed = 0;
        foreach ($assets as $asset) {
            if (File::exists($asset)) {
                if (File::isDirectory($asset)) {
                    File::deleteDirectory($asset);
                } else {
                    File::delete($asset);
                }
                $this->comment("   ✅ Removed: {$asset}");
                $removed++;
            }
        }

        if ($removed === 0) {
            $this->comment('   ℹ️  No published assets found to remove.');
        }
    }

    /**
     * Remove created files
     */
    protected function removeCreatedFiles(): void
    {
        $files = [
            // Inertia root template (only if it matches package version)
            resource_path('views/app.blade.php'),

            // JavaScript entry point (only if it matches package version)
            resource_path('js/app.js'),
        ];

        $removed = 0;
        foreach ($files as $file) {
            if (File::exists($file)) {
                // Check if file contains package-specific content before deleting
                $content = File::get($file);
                if (str_contains($content, 'inertia-resource') ||
                    str_contains($content, 'createInertiaApp') ||
                    str_contains($content, '@inertiajs/vue3')) {
                    File::delete($file);
                    $this->comment("   ✅ Removed: {$file}");
                    $removed++;
                } else {
                    $this->comment("   ⏭️  Skipped: {$file} (may have customizations)");
                }
            }
        }

        if ($removed === 0) {
            $this->comment('   ℹ️  No created files found to remove.');
        }
    }

    /**
     * Remove routes
     */
    protected function removeRoutes(): void
    {
        $routesPath = base_path('routes');

        // Remove admin.php
        $adminRoutesFile = "{$routesPath}/admin.php";
        if (File::exists($adminRoutesFile)) {
            File::delete($adminRoutesFile);
            $this->comment('   ✅ Removed: routes/admin.php');
        }

    }

    /**
     * Remove middleware
     */
    protected function removeMiddleware(): void
    {
        $middlewarePath = app_path('Http/Middleware');

        $middlewareFiles = [
            'HandleInertiaRequests.php',
        ];

        $removed = 0;
        foreach ($middlewareFiles as $file) {
            $filePath = "{$middlewarePath}/{$file}";
            if (File::exists($filePath)) {
                // Check if it's a package-generated file
                $content = File::get($filePath);
                if (str_contains($content, 'inertia-resource') ||
                    str_contains($content, 'Vue Inertia Resources')) {
                    File::delete($filePath);
                    $this->comment("   ✅ Removed: {$file}");
                    $removed++;
                } else {
                    $this->comment("   ⏭️  Skipped: {$file} (may have customizations)");
                }
            }
        }

        if ($removed === 0) {
            $this->comment('   ℹ️  No middleware files found to remove.');
        }
    }

    /**
     * Remove models
     */
    protected function removeModels(): void
    {
        $modelsPath = app_path('Models');

        $models = [];

        $removed = 0;
        foreach ($models as $model) {
            $modelPath = "{$modelsPath}/{$model}";
            if (File::exists($modelPath)) {
                // Check if it's a package-generated file
                $content = File::get($modelPath);
                if (str_contains($content, 'inertia-resource') ||
                    str_contains($content, 'Vue Inertia Resources')) {
                    File::delete($modelPath);
                    $this->comment("   ✅ Removed: {$model}");
                    $removed++;
                } else {
                    $this->comment("   ⏭️  Skipped: {$model} (may have customizations)");
                }
            }
        }

        // Note: We don't remove User model as it's typically part of the app
        if ($removed === 0) {
            $this->comment('   ℹ️  No package models found to remove.');
        }
    }

    /**
     * Remove migrations
     */
    protected function removeMigrations(): void
    {
        $migrationsPath = database_path('migrations');

        if (! File::exists($migrationsPath)) {
            $this->comment('   ℹ️  Migrations directory not found.');

            return;
        }

        $migrationFiles = glob($migrationsPath.'/*_create_user_column_preferences_table.php');

        $removed = 0;
        foreach ($migrationFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
                $filename = basename($file);
                $this->comment("   ✅ Removed: {$filename}");
                $removed++;
            }
        }

        if ($removed === 0) {
            $this->comment('   ℹ️  No package migrations found to remove.');
        } else {
            $this->warn('   ⚠️  Note: Database tables created by these migrations are not removed.');
            $this->comment('   You may need to manually drop tables: user_column_preferences');
        }
    }

    /**
     * Remove resources
     */
    protected function removeResources(): void
    {
        $resourcesPath = app_path('Inertia');

        if (! File::exists($resourcesPath)) {
            $this->comment('   ℹ️  Resources directory not found.');

            return;
        }

        // Find all resource files
        $resourceFiles = glob($resourcesPath.'/*Resource.php');
        $resourceFiles = array_merge($resourceFiles, glob($resourcesPath.'/**/*Resource.php'));

        $removed = 0;
        foreach ($resourceFiles as $file) {
            // Check if it's a package-generated file
            $content = File::get($file);
            if (str_contains($content, 'inertia-resource') ||
                str_contains($content, 'Vue Inertia Resources') ||
                str_contains($content, 'make:inertia-resource')) {
                File::delete($file);
                $filename = basename($file);
                $this->comment("   ✅ Removed: {$filename}");
                $removed++;
            }
        }

        // Also check for resource pages
        $pagesPath = resource_path('js/Pages');
        if (File::exists($pagesPath)) {
            $pageDirs = ['Users'];
            foreach ($pageDirs as $dir) {
                $dirPath = "{$pagesPath}/{$dir}";
                if (File::isDirectory($dirPath)) {
                    File::deleteDirectory($dirPath);
                    $this->comment("   ✅ Removed: Pages/{$dir}/");
                    $removed++;
                }
            }
        }

        if ($removed === 0) {
            $this->comment('   ℹ️  No package resources found to remove.');
        }
    }

    /**
     * Remove seeders
     */
    protected function removeSeeders(): void
    {
        $seedersPath = database_path('seeders');

        if (! File::exists($seedersPath)) {
            $this->comment('   ℹ️  Seeders directory not found.');

            return;
        }

        $seederFiles = [];

        $removed = 0;
        foreach ($seederFiles as $seeder) {
            $seederPath = "{$seedersPath}/{$seeder}";
            if (File::exists($seederPath)) {
                // Check if it's a package-generated file
                $content = File::get($seederPath);
                if (str_contains($content, 'inertia-resource') ||
                    str_contains($content, 'Vue Inertia Resources')) {
                    File::delete($seederPath);
                    $this->comment("   ✅ Removed: {$seeder}");
                    $removed++;
                } else {
                    $this->comment("   ⏭️  Skipped: {$seeder} (may have customizations)");
                }
            }
        }

        if ($removed === 0) {
            $this->comment('   ℹ️  No package seeders found to remove.');
        }
    }

    /**
     * Remove npm dependencies
     */
    protected function removeNpmDependencies(): void
    {
        $packageJsonPath = base_path('package.json');

        if (! File::exists($packageJsonPath)) {
            $this->comment('   ℹ️  package.json not found.');

            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);

        if (! isset($packageJson['dependencies'])) {
            $this->comment('   ℹ️  No dependencies found in package.json.');

            return;
        }

        // Package-specific dependencies to remove
        $packageDependencies = [
            '@inertiajs/vue3',
            '@heroicons/vue',
            'axios',
            'ziggy',
        ];

        $removed = [];
        foreach ($packageDependencies as $dep) {
            if (isset($packageJson['dependencies'][$dep])) {
                unset($packageJson['dependencies'][$dep]);
                $removed[] = $dep;
            }
        }

        if (empty($removed)) {
            $this->comment('   ℹ️  No package-specific dependencies found to remove.');

            return;
        }

        File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        foreach ($removed as $dep) {
            $this->comment("   ✅ Removed dependency: {$dep}");
        }

        $this->comment('   💡 Run "npm install" to update node_modules');
    }

    /**
     * Restore package.json (remove package-specific scripts and config)
     */
    protected function restorePackageJson(): void
    {
        $packageJsonPath = base_path('package.json');

        if (! File::exists($packageJsonPath)) {
            $this->comment('   ℹ️  package.json not found.');

            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);

        // Remove package-specific scripts if they exist
        if (isset($packageJson['scripts'])) {
            $scriptsToRemove = ['dev', 'build', 'watch'];
            $removed = false;

            foreach ($scriptsToRemove as $script) {
                if (isset($packageJson['scripts'][$script]) &&
                    str_contains($packageJson['scripts'][$script], 'vite')) {
                    // Only remove if it's the standard Vite setup
                    unset($packageJson['scripts'][$script]);
                    $removed = true;
                }
            }

            if ($removed) {
                File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->comment('   ✅ Cleaned up package.json scripts');
            } else {
                $this->comment('   ℹ️  No package-specific scripts found to remove.');
            }
        }
    }
}
