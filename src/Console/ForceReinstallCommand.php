<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ForceReinstallCommand extends InstallCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-inertia-resources:force-reinstall {--fresh : Run fresh migrations (drop all tables)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force reinstall Vue Inertia Resources with overwrites and minimal prompts';

    /**
     * Whether to run fresh migrations
     *
     * @var bool
     */
    protected $runFreshMigrations = false;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Force Reinstalling Vue Inertia Resources...');
        $this->newLine();

        // Check if --fresh flag is provided
        $this->runFreshMigrations = $this->option('fresh');

        // Ask about fresh migrations if --fresh not provided
        if (! $this->runFreshMigrations) {
            $this->runFreshMigrations = $this->confirm('Do you want to run fresh migrations (drop all tables)?', false);
        }

        // Track which resources were created
        $userResourceCreated = false;

        $this->newLine();

        // Use default for Enhanced Fields (false) - no prompt
        $this->useEnhancedFields = false;
        $this->newLine();

        // Merge package.json dependencies
        $this->info('📦 Merging npm dependencies...');
        $this->mergePackageJson();
        $this->newLine();

        // Publish Tailwind config (force overwrite)
        $this->info('🎨 Publishing Tailwind configuration...');
        $this->publishTailwindConfig();
        $this->newLine();

        // Publish all assets (force overwrite)
        $this->info('📁 Publishing package assets...');
        $this->publishAssetsSafely();
        $this->newLine();

        // Run migrations (fresh or regular based on option)
        $this->info('🔄 Running database migrations...');
        $this->runMigrations();
        $this->newLine();

        // Create Inertia root template (force overwrite)
        $this->info('📄 Creating Inertia root template...');
        $this->createInertiaRootTemplate();
        $this->newLine();

        // Create JavaScript entry point (force overwrite)
        $this->info('📜 Creating JavaScript entry point...');
        $this->createJavaScriptEntryPoint();
        $this->newLine();

        // Clean up incorrectly placed routes in web.php
        $this->info('🧹 Cleaning up routes in web.php...');
        $this->cleanupWebRoutes();
        $this->newLine();

        // Create UI components and composables (force overwrite)
        $this->info('🎨 Creating UI components and composables...');
        $this->createUIComponents();
        $this->newLine();

        // Install npm dependencies
        $this->info('📥 Installing npm dependencies (this may take a few minutes)...');
        $this->newLine();

        passthru('npm install', $returnCode);

        if ($returnCode !== 0) {
            $this->warn('⚠️  First npm install attempt failed. Trying with --legacy-peer-deps...');
            $this->newLine();

            passthru('npm install --legacy-peer-deps', $returnCode);

            if ($returnCode !== 0) {
                $this->error('❌ npm install failed. Please run it manually: npm install --legacy-peer-deps');
                $this->newLine();
                $this->warn('⚠️  Important: You must run "npm install" before using Vite or building assets.');

                return 1;
            }
        }

        $this->newLine();
        $this->info('✅ npm install completed successfully!');

        // Check and fix vite.config.js if needed
        $this->info('🔧 Checking vite.config.js...');
        $this->fixViteConfig();
        $this->newLine();

        // Track which resources were created
        $userResourceCreated = false;

        // Check User model and migration (use defaults)
        $this->info('👤 Checking User model and migration...');
        $userModelExists = $this->checkUserModelExists();
        $userMigrationExists = $this->checkMigrationExists('create_users_table');

        if (! $userModelExists) {
            $this->warn('⚠️  User model not found.');
            // Use default: yes
            $this->createUserModel();
        } else {
            $this->comment('ℹ️  User model already exists.');
        }

        if (! $userMigrationExists) {
            $this->warn('⚠️  User migration not found.');
            // Use default: yes
            $this->createUserMigration();
        } else {
            $this->comment('ℹ️  User migration already exists.');
        }
        $this->newLine();

        // Use default: yes for User Resource
        $this->info('📦 Creating User Resource...');
        $this->newLine();

        // Check if User model exists
        $userModel = 'App\\Models\\User';
        if (! class_exists($userModel)) {
            // Try alternative namespace
            $userModel = 'App\\User';
            if (! class_exists($userModel)) {
                $this->warn('⚠️  User model not found. Skipping User Resource creation.');
                $this->comment('   Please create the User Resource manually: php artisan make:inertia-resource "App\\Models\\User" --all');
                $this->newLine();
            } else {
                $this->call('make:inertia-resource', [
                    'model' => $userModel,
                    '--all' => true,
                ]);
                $this->newLine();
                $userResourceCreated = true;
            }
        } else {
            $this->call('make:inertia-resource', [
                'model' => $userModel,
                '--all' => true,
            ]);
            $this->newLine();
            $userResourceCreated = true;
        }

        // Create Cursor and Laravel Boost rules (force overwrite)
        $this->info('📝 Creating Cursor and Laravel Boost rules...');
        $this->createCursorRules(true);
        $this->newLine();

        $this->newLine();
        $this->info('✅ Vue Inertia Resources force reinstall complete!');
        $this->newLine();

        $this->newLine();
        $this->comment('Next steps:');
        $this->comment('1. Update your vite.config.js to include Tailwind CSS 4 plugin (if not already done)');
        $this->comment('2. Ensure your CSS file imports Tailwind: @import "tailwindcss";');
        $this->comment('3. Start your development server: npm run dev');

        return 0;
    }

    /**
     * Publish assets with force overwrite
     */
    protected function publishAssetsSafely(): void
    {
        // Always publish everything with force
        $this->call('vendor:publish', [
            '--tag' => 'inertia-resource',
            '--force' => true,
        ]);
    }

    /**
     * Publish Tailwind configuration with force overwrite
     */
    protected function publishTailwindConfig(): void
    {
        $tailwindConfigPath = base_path('tailwind.config.js');
        $packageConfigPath = __DIR__.'/../../tailwind.config.js';

        if (File::exists($packageConfigPath)) {
            // Force overwrite
            File::copy($packageConfigPath, $tailwindConfigPath);
            $this->info('Published tailwind.config.js (overwritten)');
        }
    }

    /**
     * Run database migrations (fresh or regular)
     */
    protected function runMigrations(): void
    {
        try {
            if ($this->runFreshMigrations) {
                $this->info('⚠️  Running fresh migrations (dropping all tables)...');
                $this->call('migrate:fresh', ['--force' => true]);
                $this->info('✅ Fresh migrations completed successfully.');
            } else {
                // Check if migrations table exists (database is set up)
                if (! \Illuminate\Support\Facades\Schema::hasTable('migrations')) {
                    $this->comment('ℹ️  Migrations table does not exist. Running initial migrations...');
                }

                // Check if there are pending migrations
                $this->call('migrate', ['--force' => true]);
                $this->info('✅ Migrations completed successfully.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if error is about table already existing
            if (str_contains($e->getMessage(), 'already exists')) {
                $this->warn('⚠️  Some tables already exist in the database.');
                $this->comment('   This usually means migrations were run before but not recorded in the migrations table.');
                $this->comment('   You may need to manually mark migrations as run or reset the database.');
                $this->newLine();
                $this->comment('   Options:');
                $this->comment('   1. Reset database: php artisan migrate:fresh');
                $this->comment('   2. Mark migrations as run: php artisan migrate --pretend (then manually insert into migrations table)');
            } else {
                $this->warn('⚠️  Migration error: '.$e->getMessage());
                $this->comment('   You may need to run migrations manually: php artisan migrate');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Migration error: '.$e->getMessage());
            $this->comment('   You may need to run migrations manually: php artisan migrate');
        }
    }

    /**
     * Create Inertia root template with force overwrite
     */
    protected function createInertiaRootTemplate(): void
    {
        $viewsPath = resource_path('views');
        $appBladePath = $viewsPath.'/app.blade.php';
        $appBladeStub = __DIR__.'/../../stubs/app.blade.php.stub';

        // Create views directory if it doesn't exist
        if (! File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        if (File::exists($appBladeStub)) {
            // Force overwrite
            File::copy($appBladeStub, $appBladePath);
            $this->info('Created/Updated app.blade.php');
        } else {
            $this->warn('⚠️  app.blade.php.stub not found. Please create resources/views/app.blade.php manually.');
        }
    }

    /**
     * Create JavaScript entry point with force overwrite
     */
    protected function createJavaScriptEntryPoint(): void
    {
        $jsPath = resource_path('js');
        $cssPath = resource_path('css');
        $appJsPath = $jsPath.'/app.js';
        $bootstrapJsPath = $jsPath.'/bootstrap.js';
        $appCssPath = $cssPath.'/app.css';
        $appJsStub = __DIR__.'/../../stubs/app.js.stub';

        // Create js directory if it doesn't exist
        if (! File::exists($jsPath)) {
            File::makeDirectory($jsPath, 0755, true);
        }

        // Create css directory if it doesn't exist
        if (! File::exists($cssPath)) {
            File::makeDirectory($cssPath, 0755, true);
        }

        // Create app.js (force overwrite)
        if (File::exists($appJsStub)) {
            File::copy($appJsStub, $appJsPath);
            $this->info('Created/Updated app.js');
        } else {
            $this->warn('⚠️  app.js.stub not found. Please create resources/js/app.js manually.');
        }

        // Create bootstrap.js if it doesn't exist (or force overwrite)
        $bootstrapContent = <<<'JS'
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
JS;
        File::put($bootstrapJsPath, $bootstrapContent);
        $this->info('Created/Updated bootstrap.js');

        // Create app.css if it doesn't exist (or force overwrite)
        $appCssContent = <<<'CSS'
@import "tailwindcss";
CSS;
        File::put($appCssPath, $appCssContent);
        $this->info('Created/Updated app.css');
    }

    /**
     * Create UI components and composables with force overwrite (from vendor)
     */
    protected function createUIComponents(): void
    {
        $componentsPath = resource_path('js/Components');
        $uiPath = $componentsPath.'/UI';
        $composablesPath = resource_path('js/Composables');
        $vendorPath = resource_path('js/vendor/inertia-resource');

        // Create directories if they don't exist
        if (! File::exists($uiPath)) {
            File::makeDirectory($uiPath, 0755, true);
        }
        if (! File::exists($composablesPath)) {
            File::makeDirectory($composablesPath, 0755, true);
        }

        // Card component (force overwrite from vendor)
        $cardSource = $vendorPath.'/Components/UI/Card.vue';
        $cardPath = $uiPath.'/Card.vue';
        if (File::exists($cardSource)) {
            File::copy($cardSource, $cardPath);
            $this->info('Created/Updated Card.vue (from vendor)');
        } else {
            $this->warn('⚠️  Card.vue not found in vendor. Make sure to publish assets first.');
        }

        // Badge component (force overwrite from vendor)
        $badgeSource = $vendorPath.'/Components/UI/Badge.vue';
        $badgePath = $uiPath.'/Badge.vue';
        if (File::exists($badgeSource)) {
            File::copy($badgeSource, $badgePath);
            $this->info('Created/Updated Badge.vue (from vendor)');
        } else {
            $this->warn('⚠️  Badge.vue not found in vendor. Make sure to publish assets first.');
        }

        // Pagination component (force overwrite from vendor)
        $paginationSource = $vendorPath.'/Components/UI/Pagination.vue';
        $paginationPath = $uiPath.'/Pagination.vue';
        if (File::exists($paginationSource)) {
            File::copy($paginationSource, $paginationPath);
            $this->info('Created/Updated Pagination.vue (from vendor)');
        } else {
            $this->warn('⚠️  Pagination.vue not found in vendor. Make sure to publish assets first.');
        }

        // useFieldVisibility composable (force overwrite from vendor)
        $composableSource = $vendorPath.'/Composables/useFieldVisibility.js';
        $composablePath = $composablesPath.'/useFieldVisibility.js';
        if (File::exists($composableSource)) {
            File::copy($composableSource, $composablePath);
            $this->info('Created/Updated useFieldVisibility.js (from vendor)');
        } else {
            $this->warn('⚠️  useFieldVisibility.js not found in vendor. Make sure to publish assets first.');
        }
    }
}
