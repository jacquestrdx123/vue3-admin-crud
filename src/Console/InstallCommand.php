<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-inertia-resources:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Vue Inertia Resources npm dependencies and configuration';

    /**
     * Whether to add enhanced fields (first_name, last_name, email, mobile_number) to User model
     *
     * @var bool
     */
    protected $useEnhancedFields = false;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Installing Vue Inertia Resources...');
        $this->newLine();

        // Track which resources were created
        $userResourceCreated = false;

        // Ask if user wants enhanced fields for User model
        $this->info('📋 Enhanced Fields Option');
        $this->comment('   You can add the following fields to User model:');
        $this->comment('   - first_name');
        $this->comment('   - last_name');
        $this->comment('   - email');
        $this->comment('   - mobile_number');
        $this->newLine();
        $this->useEnhancedFields = $this->confirm('Do you want to add these enhanced fields to User model?', false);
        $this->newLine();

        // Merge package.json dependencies
        $this->info('📦 Merging npm dependencies...');
        $this->mergePackageJson();
        $this->newLine();

        // Publish Tailwind config
        $this->info('🎨 Publishing Tailwind configuration...');
        $this->publishTailwindConfig();
        $this->newLine();

        // Publish all assets (but check migrations first to avoid duplicates)
        $this->info('📁 Publishing package assets...');
        $this->publishAssetsSafely();
        $this->newLine();

        // Run migrations
        $this->info('🔄 Running database migrations...');
        $this->runMigrations();
        $this->newLine();

        // Create Inertia root template
        $this->info('📄 Creating Inertia root template...');
        $this->createInertiaRootTemplate();
        $this->newLine();

        // Create JavaScript entry point
        $this->info('📜 Creating JavaScript entry point...');
        $this->createJavaScriptEntryPoint();
        $this->newLine();

        // Clean up incorrectly placed routes in web.php
        $this->info('🧹 Cleaning up routes in web.php...');
        $this->cleanupWebRoutes();
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

        // Check User model and migration
        $this->info('👤 Checking User model and migration...');
        $userModelExists = $this->checkUserModelExists();
        $userMigrationExists = $this->checkMigrationExists('create_users_table');

        if (! $userModelExists) {
            $this->warn('⚠️  User model not found.');
            if ($this->confirm('Do you want to create the User model?', true)) {
                $this->createUserModel();
                if ($this->useEnhancedFields) {
                    $this->updateUserModel();
                }
            }
        } else {
            $this->comment('ℹ️  User model already exists.');
            if ($this->useEnhancedFields) {
                $this->updateUserModel();
            }
        }

        if (! $userMigrationExists) {
            $this->warn('⚠️  User migration not found.');
            if ($this->confirm('Do you want to create the User migration?', true)) {
                $this->createUserMigration();
            }
        } else {
            $this->comment('ℹ️  User migration already exists.');
            if ($this->useEnhancedFields) {
                $this->updateUserMigration();
            }
        }
        $this->newLine();

        // Ask if user wants to create the initial User Resource
        if ($this->confirm('Do you want to create a Resource for the User model?', true)) {
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
                if ($this->useEnhancedFields) {
                    $this->updateUserResource();
                }
            }
        }

        // Create Cursor and Laravel Boost rules
        $this->info('📝 Creating Cursor and Laravel Boost rules...');
        $this->createCursorRules();
        $this->newLine();

        $this->newLine();
        $this->info('✅ Vue Inertia Resources installation complete!');
        $this->newLine();

        $this->newLine();
        $this->comment('Next steps:');
        $this->comment('1. Update your vite.config.js to include Tailwind CSS 4 plugin (if not already done)');
        $this->comment('2. Ensure your CSS file imports Tailwind: @import "tailwindcss";');
        $this->comment('3. Start your development server: npm run dev');

        return 0;
    }

    /**
     * Publish assets safely, checking for existing migrations first
     */
    protected function publishAssetsSafely(): void
    {
        $migrationsPath = database_path('migrations');

        // Check if package migrations already exist
        $userColumnPrefsMigration = glob($migrationsPath.'/*_create_user_column_preferences_table.php');

        $hasAllMigrations = ! empty($userColumnPrefsMigration);

        if ($hasAllMigrations) {
            $this->comment('   ℹ️  Package migrations already exist. Skipping migration publishing.');
            $this->comment('   ℹ️  Publishing other assets only...');

            // Publish everything except migrations
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-config',
                '--force' => false,
            ]);
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-components',
                '--force' => false,
            ]);
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-tailwind',
                '--force' => false,
            ]);
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-vite',
                '--force' => false,
            ]);
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-assets',
                '--force' => false,
            ]);
        } else {
            // Publish everything including migrations
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource',
                '--force' => false,
            ]);
        }
    }

    /**
     * Run database migrations
     */
    protected function runMigrations(): void
    {
        try {
            // Check if migrations table exists (database is set up)
            if (! \Illuminate\Support\Facades\Schema::hasTable('migrations')) {
                $this->comment('ℹ️  Migrations table does not exist. Running initial migrations...');
            }

            // Check if there are pending migrations
            $this->call('migrate', ['--force' => true]);
            $this->info('✅ Migrations completed successfully.');
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
     * Merge package.json dependencies
     */
    protected function mergePackageJson(): void
    {
        $packageJsonPath = base_path('package.json');
        $packageDependencies = [
            'vue' => '^3.4.0',
            '@vitejs/plugin-vue' => '^5.0.0',
            'tailwindcss' => '^4.0.0',
            '@tailwindcss/vite' => '^4.0.0',
            'autoprefixer' => '^10.4.20',
            'postcss' => '^8.4.47',
            '@inertiajs/vue3' => '^1.0.0',
            '@headlessui/vue' => '^1.7.21',
            '@heroicons/vue' => '^2.1.1',
            'axios' => '^1.7.7',
        ];

        $packageDevDependencies = [
            'vite' => '^5.4.0',
            'laravel-vite-plugin' => '^1.0.0',
        ];

        if (File::exists($packageJsonPath)) {
            $existingPackageJson = json_decode(File::get($packageJsonPath), true);

            // Merge dependencies
            if (! isset($existingPackageJson['dependencies'])) {
                $existingPackageJson['dependencies'] = [];
            }
            $existingPackageJson['dependencies'] = array_merge(
                $existingPackageJson['dependencies'],
                $packageDependencies
            );

            // Merge devDependencies
            if (! isset($existingPackageJson['devDependencies'])) {
                $existingPackageJson['devDependencies'] = [];
            }
            $existingPackageJson['devDependencies'] = array_merge(
                $existingPackageJson['devDependencies'],
                $packageDevDependencies
            );

            // Ensure type is module if not set
            if (! isset($existingPackageJson['type'])) {
                $existingPackageJson['type'] = 'module';
            }

            File::put($packageJsonPath, json_encode($existingPackageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated package.json with Vue Inertia Resources dependencies.');
        } else {
            // Create new package.json
            $newPackageJson = [
                'private' => true,
                'type' => 'module',
                'scripts' => [
                    'dev' => 'vite',
                    'build' => 'vite build',
                ],
                'dependencies' => $packageDependencies,
                'devDependencies' => $packageDevDependencies,
            ];

            File::put($packageJsonPath, json_encode($newPackageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Created package.json with Vue Inertia Resources dependencies.');
        }
    }

    /**
     * Publish Tailwind configuration
     */
    protected function publishTailwindConfig(): void
    {
        $tailwindConfigPath = base_path('tailwind.config.js');
        $packageConfigPath = __DIR__.'/../../tailwind.config.js';

        if (File::exists($packageConfigPath)) {
            if (File::exists($tailwindConfigPath)) {
                $this->warn('tailwind.config.js already exists. Please merge manually.');
            } else {
                File::copy($packageConfigPath, $tailwindConfigPath);
                $this->info('Published tailwind.config.js');
            }
        }
    }

    /**
     * Create Inertia root template
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
            if (File::exists($appBladePath)) {
                // Check if the existing file has incorrect content
                $existingContent = File::get($appBladePath);
                $hasIncorrectVite = str_contains($existingContent, 'resources/js/Pages/{$') ||
                                    str_contains($existingContent, 'Pages/{$page') ||
                                    preg_match('/@vite\([^)]*Pages[^)]*\)/', $existingContent);
                $hasExtraDiv = str_contains($existingContent, '<div id="app">') && str_contains($existingContent, '<slot />');

                if ($hasIncorrectVite || $hasExtraDiv) {
                    $this->warn('⚠️  app.blade.php contains incorrect content. Fixing...');
                    File::copy($appBladeStub, $appBladePath);
                    $this->info('✅ Fixed app.blade.php with correct Inertia template.');
                } else {
                    $this->warn('app.blade.php already exists. Skipping...');
                }
            } else {
                File::copy($appBladeStub, $appBladePath);
                $this->info('Created app.blade.php');
            }
        } else {
            $this->warn('⚠️  app.blade.php.stub not found. Please create resources/views/app.blade.php manually.');
        }
    }

    /**
     * Create JavaScript entry point
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

        // Create app.js
        if (File::exists($appJsStub)) {
            if (File::exists($appJsPath)) {
                // Check if the existing file has proper Inertia setup
                $existingContent = File::get($appJsPath);
                $hasInertiaSetup = str_contains($existingContent, 'createInertiaApp') &&
                                   str_contains($existingContent, 'resolvePageComponent');

                if (! $hasInertiaSetup) {
                    $this->warn('⚠️  app.js is missing Inertia setup. Fixing...');
                    File::copy($appJsStub, $appJsPath);
                    $this->info('✅ Fixed app.js with correct Inertia setup.');
                } else {
                    $this->comment('app.js already exists with Inertia setup. Skipping...');
                }
            } else {
                File::copy($appJsStub, $appJsPath);
                $this->info('Created app.js');
            }
        } else {
            $this->warn('⚠️  app.js.stub not found. Please create resources/js/app.js manually.');
        }

        // Create bootstrap.js if it doesn't exist
        if (! File::exists($bootstrapJsPath)) {
            $bootstrapContent = <<<'JS'
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
JS;
            File::put($bootstrapJsPath, $bootstrapContent);
            $this->info('Created bootstrap.js');
        }

        // Create app.css if it doesn't exist
        if (! File::exists($appCssPath)) {
            $appCssContent = <<<'CSS'
@import "tailwindcss";
CSS;
            File::put($appCssPath, $appCssContent);
            $this->info('Created app.css');
        }
    }

    /**
     * Clean up incorrectly placed routes from web.php
     */
    protected function cleanupWebRoutes(): void
    {
        $webRoutesFile = base_path('routes/web.php');

        if (! File::exists($webRoutesFile)) {
            return;
        }

        $webRoutesContent = File::get($webRoutesFile);
        $originalContent = $webRoutesContent;
        $cleaned = false;

        // Remove admin routes that were incorrectly added directly to web.php
        // Look for Route::prefix('admin') groups that are not part of a require statement
        $lines = explode("\n", $webRoutesContent);
        $cleanedLines = [];
        $skipUntilBraceCount = null;
        $braceCount = 0;
        $inAdminGroup = false;
        $inRequireStatement = false;

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Check if this line is a require statement for admin.php
            if (preg_match("/require\s+__DIR__\s*\.\s*['\"]\/admin\.php['\"]/", $line)) {
                $cleanedLines[] = $line;

                continue;
            }

            // Check if we're starting an admin route group (not in a require statement)
            if (! $inAdminGroup && preg_match("/Route::prefix\(['\"]admin['\"]\)/", $line)) {
                $inAdminGroup = true;
                $skipUntilBraceCount = $braceCount;
                $braceCount += substr_count($line, '(') - substr_count($line, ')');
                $cleaned = true;

                continue; // Skip this line
            }

            // If we're skipping an admin route group, track braces
            if ($inAdminGroup && $skipUntilBraceCount !== null) {
                $braceCount += substr_count($line, '(') - substr_count($line, ')');

                // Check if we've closed the route group (brace count back to original or less)
                if ($braceCount <= $skipUntilBraceCount) {
                    $inAdminGroup = false;
                    $skipUntilBraceCount = null;
                    $braceCount = 0;
                }

                continue; // Skip this line
            }

            // Normal line - keep it
            $cleanedLines[] = $line;
        }

        if ($cleaned) {
            $webRoutesContent = implode("\n", $cleanedLines);
            File::put($webRoutesFile, $webRoutesContent);
            $this->info('✅ Removed incorrectly placed admin routes from web.php');
            $this->comment('   Routes should be in admin.php, not directly in web.php');
        } else {
            $this->comment('✅ No cleanup needed in web.php');
        }
    }

    /**
     * Fix vite.config.js if it has incorrect import
     */
    protected function fixViteConfig(): void
    {
        $viteConfigPath = base_path('vite.config.js');

        if (! File::exists($viteConfigPath)) {
            // Publish the stub if vite.config.js doesn't exist
            $stubPath = __DIR__.'/../../stubs/vite.config.js.stub';
            if (File::exists($stubPath)) {
                File::copy($stubPath, $viteConfigPath);
                $this->info('Created vite.config.js with correct configuration.');
            }

            return;
        }

        $viteConfigContent = File::get($viteConfigPath);
        $needsFix = false;
        $fixedContent = $viteConfigContent;

        // Check for incorrect import: @laravel/vite-plugin (doesn't exist)
        if (str_contains($viteConfigContent, '@laravel/vite-plugin')) {
            $this->warn('⚠️  Found incorrect import in vite.config.js: @laravel/vite-plugin');
            $this->comment('   Fixing to: laravel-vite-plugin');

            // Fix the import - handle both single and named imports
            $fixedContent = preg_replace(
                "/import\s+laravel(?:\s*,\s*\{[^}]*\})?\s+from\s+['\"]@laravel\/vite-plugin['\"]/",
                "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                $fixedContent
            );

            // Also fix simple imports
            $fixedContent = preg_replace(
                "/import\s+laravel\s+from\s+['\"]@laravel\/vite-plugin['\"]/",
                "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                $fixedContent
            );

            $needsFix = true;
        }

        // Check for old import: laravel/vite-plugin (old pattern)
        if (str_contains($fixedContent, "from 'laravel/vite-plugin'") ||
            str_contains($fixedContent, 'from "laravel/vite-plugin"')) {

            if (! $needsFix) {
                $this->warn('⚠️  Found old import pattern in vite.config.js: laravel/vite-plugin');
                $this->comment('   Updating to: laravel-vite-plugin');
            }

            // Fix the import to use the correct package name
            $fixedContent = preg_replace(
                "/import\s+laravel(?:\s*,\s*\{[^}]*\})?\s+from\s+['\"]laravel\/vite-plugin['\"]/",
                "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                $fixedContent
            );

            // Also fix simple imports
            $fixedContent = preg_replace(
                "/import\s+laravel\s+from\s+['\"]laravel\/vite-plugin['\"]/",
                "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                $fixedContent
            );

            $needsFix = true;
        }

        // Check for missing refreshPaths import when laravel-vite-plugin is imported
        // Pattern: import laravel from 'laravel-vite-plugin' (without { refreshPaths })
        if (preg_match("/import\s+laravel\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent) &&
            ! preg_match("/import\s+laravel\s*,\s*\{[^}]*refreshPaths[^}]*\}\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent)) {

            if (! $needsFix) {
                $this->warn('⚠️  Found laravel-vite-plugin import missing refreshPaths');
                $this->comment('   Adding refreshPaths to import...');
            }

            // Add refreshPaths to the import
            $fixedContent = preg_replace(
                "/import\s+laravel\s+from\s+['\"]laravel-vite-plugin['\"]/",
                "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                $fixedContent
            );

            $needsFix = true;
        }

        // Check if refresh: refreshPaths is used but refreshPaths isn't imported
        if (str_contains($fixedContent, 'refresh: refreshPaths') &&
            ! preg_match("/import\s+laravel\s*,\s*\{[^}]*refreshPaths[^}]*\}\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent)) {

            if (! $needsFix) {
                $this->warn('⚠️  Found refresh: refreshPaths usage without import');
                $this->comment('   Adding refreshPaths to import...');
            }

            // Add refreshPaths to existing laravel import
            if (preg_match("/import\s+laravel\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent)) {
                $fixedContent = preg_replace(
                    "/import\s+laravel\s+from\s+['\"]laravel-vite-plugin['\"]/",
                    "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                    $fixedContent
                );
            } else {
                // If laravel import doesn't exist, add it before other imports
                $fixedContent = preg_replace(
                    "/(import\s+[^;]+;)/",
                    "import laravel, { refreshPaths } from 'laravel-vite-plugin';\n$1",
                    $fixedContent,
                    1
                );
            }

            $needsFix = true;
        }

        // Check if refresh is set to true instead of refreshPaths
        if (str_contains($fixedContent, 'refresh: true') &&
            str_contains($fixedContent, "from 'laravel-vite-plugin'")) {

            if (! $needsFix) {
                $this->comment('Updating refresh configuration...');
            }

            $fixedContent = preg_replace(
                "/refresh:\s*true/",
                'refresh: refreshPaths',
                $fixedContent
            );

            // Ensure refreshPaths is imported
            if (! preg_match("/import\s+laravel\s*,\s*\{[^}]*refreshPaths[^}]*\}\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent)) {
                $fixedContent = preg_replace(
                    "/import\s+laravel\s+from\s+['\"]laravel-vite-plugin['\"]/",
                    "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                    $fixedContent
                );
            }

            $needsFix = true;
        }

        // Check for missing Vue plugin
        $hasVueImport = str_contains($fixedContent, '@vitejs/plugin-vue') || str_contains($fixedContent, '@vitejs/plugin-vue');
        $hasVuePlugin = str_contains($fixedContent, 'vue(') || str_contains($fixedContent, 'vue({');

        if (! $hasVueImport || ! $hasVuePlugin) {
            if (! $needsFix) {
                $this->warn('⚠️  Missing Vue plugin in vite.config.js');
                $this->comment('   Adding Vue plugin...');
            }

            // Add Vue import if missing
            if (! $hasVueImport) {
                // Find the last import statement and add Vue import after it
                if (preg_match("/(import\s+[^;]+;)\s*$/m", $fixedContent, $matches)) {
                    $fixedContent = preg_replace(
                        "/(import\s+[^;]+;)\s*$/m",
                        "$1\nimport vue from '@vitejs/plugin-vue';",
                        $fixedContent,
                        1
                    );
                } else {
                    // Add after defineConfig import
                    $fixedContent = preg_replace(
                        "/(import\s+\{[^}]+\}\s+from\s+['\"]vite['\"];)/",
                        "$1\nimport vue from '@vitejs/plugin-vue';",
                        $fixedContent
                    );
                }
            }

            // Add Vue plugin to plugins array if missing
            if (! $hasVuePlugin) {
                // Find the laravel plugin and add vue after it
                if (preg_match("/(laravel\(\{[^}]*\}\)),?\s*/", $fixedContent, $matches)) {
                    $vuePlugin = "\n        vue({\n            template: {\n                transformAssetUrls: {\n                    base: null,\n                    includeAbsolute: false,\n                },\n            },\n        }),";
                    $fixedContent = preg_replace(
                        "/(laravel\(\{[^}]*\}\)),?\s*/",
                        '$1,'.$vuePlugin,
                        $fixedContent
                    );
                } else {
                    // Add vue plugin before tailwindcss if it exists
                    if (str_contains($fixedContent, 'tailwindcss()')) {
                        $fixedContent = preg_replace(
                            "/(tailwindcss\(\))/",
                            "vue({\n            template: {\n                transformAssetUrls: {\n                    base: null,\n                    includeAbsolute: false,\n                },\n            },\n        }),\n        $1",
                            $fixedContent
                        );
                    } else {
                        // Add at the end of plugins array
                        $fixedContent = preg_replace(
                            "/(plugins:\s*\[)([^\]]*)(\])/s",
                            "$1$2        vue({\n            template: {\n                transformAssetUrls: {\n                    base: null,\n                    includeAbsolute: false,\n                },\n            },\n        }),\n$3",
                            $fixedContent
                        );
                    }
                }
            }

            $needsFix = true;
        }

        // Check for missing resolve alias
        if (! str_contains($fixedContent, 'resolve:') || ! str_contains($fixedContent, "'@':") || ! str_contains($fixedContent, "'@': '/resources/js'")) {
            if (! $needsFix) {
                $this->warn('⚠️  Missing resolve alias in vite.config.js');
                $this->comment('   Adding resolve alias...');
            }

            // Check if resolve already exists
            if (str_contains($fixedContent, 'resolve:')) {
                // Add alias to existing resolve
                if (! str_contains($fixedContent, "'@':") && ! str_contains($fixedContent, '"@":')) {
                    $fixedContent = preg_replace(
                        "/(resolve:\s*\{)/",
                        "$1\n        alias: {\n            '@': '/resources/js',\n        },",
                        $fixedContent
                    );
                }
            } else {
                // Add resolve section before closing brace of defineConfig
                if (preg_match("/(plugins:\s*\[[^\]]+\]),?\s*(\})/s", $fixedContent, $matches)) {
                    $fixedContent = preg_replace(
                        "/(plugins:\s*\[[^\]]+\]),?\s*(\})/s",
                        "$1,\n    resolve: {\n        alias: {\n            '@': '/resources/js',\n        },\n    }$2",
                        $fixedContent
                    );
                }
            }

            $needsFix = true;
        }

        if ($needsFix && $fixedContent !== $viteConfigContent) {
            File::put($viteConfigPath, $fixedContent);
            $this->info('✅ Fixed vite.config.js import and configuration.');
        } elseif (str_contains($viteConfigContent, "from 'laravel-vite-plugin'") &&
                   preg_match("/import\s+laravel\s*,\s*\{[^}]*refreshPaths[^}]*\}\s+from\s+['\"]laravel-vite-plugin['\"]/", $viteConfigContent) &&
                   str_contains($viteConfigContent, '@vitejs/plugin-vue') &&
                   str_contains($viteConfigContent, 'resolve:')) {
            $this->comment('✓ vite.config.js already has correct configuration.');
        }
    }

    /**
     * Create UI components and composables
     */
    protected function createUIComponents(): void
    {
        // UI components and composables are now in vendor at resources/js/vendor/inertia-resource/
        // Users can override by creating files in resources/js/Components/ or resources/js/Composables/
        // Vite will automatically prefer app files over vendor files
        $this->comment('   UI components and composables are available from vendor');
        $this->comment('   To customize, create files in resources/js/Components/ or resources/js/Composables/');
        $this->comment('   Example: Create resources/js/Components/UI/Card.vue to override the vendor version');
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

    /**
     * Run a seeder class
     */
    protected function runSeeder(string $seederClass): void
    {
        try {
            $this->info("🌱 Running {$seederClass}...");
            $this->call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);
            $this->info("✅ {$seederClass} completed successfully.");
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not run {$seederClass}: ".$e->getMessage());
            $this->comment("   Please run manually: php artisan db:seed --class={$seederClass}");
        }
    }

    /**
     * Check if a migration exists
     */
    protected function checkMigrationExists(string $migrationName): bool
    {
        $migrationsPath = database_path('migrations');
        $pattern = $migrationsPath.'/*_'.$migrationName.'.php';
        $migrations = glob($pattern);

        return ! empty($migrations);
    }

    /**
     * Check if User model exists
     */
    protected function checkUserModelExists(): bool
    {
        $userModel = 'App\\Models\\User';
        if (class_exists($userModel)) {
            return true;
        }

        $userModel = 'App\\User';
        if (class_exists($userModel)) {
            return true;
        }

        // Check if file exists
        $modelPath = app_path('Models/User.php');
        if (File::exists($modelPath)) {
            return true;
        }

        $modelPath = app_path('User.php');
        if (File::exists($modelPath)) {
            return true;
        }

        return false;
    }

    /**
     * Create User model
     */
    protected function createUserModel(): void
    {
        $modelPath = app_path('Models/User.php');
        $modelDir = app_path('Models');

        if (! File::exists($modelDir)) {
            File::makeDirectory($modelDir, 0755, true);
        }

        if (File::exists($modelPath)) {
            $this->comment('ℹ️  User model already exists.');

            return;
        }

        $this->call('make:model', [
            'name' => 'User',
        ]);

        $this->info('✅ Created User model.');
    }

    /**
     * Create User migration
     */
    protected function createUserMigration(): void
    {
        if ($this->checkMigrationExists('create_users_table')) {
            $this->comment('ℹ️  User migration already exists.');
            if ($this->useEnhancedFields) {
                $this->updateUserMigration();
            }

            return;
        }

        $this->call('make:migration', [
            'name' => 'create_users_table',
        ]);

        $this->info('✅ Created User migration.');

        if ($this->useEnhancedFields) {
            $this->updateUserMigration();
        } else {
            $this->comment('   Note: You may need to customize the migration file.');
        }
    }

    /**
     * Update User migration with enhanced fields
     */
    protected function updateUserMigration(): void
    {
        $migrationFiles = glob(database_path('migrations/*_create_users_table.php'));
        if (empty($migrationFiles)) {
            return;
        }

        $migrationFile = $migrationFiles[0];
        $content = File::get($migrationFile);

        // Check if fields already exist
        if (strpos($content, 'first_name') !== false) {
            $this->comment('   ℹ️  Enhanced fields already exist in User migration.');

            return;
        }

        // Find the Schema::create block and add fields
        // Look for the id() line and add fields after it, before timestamps
        if (preg_match('/(\$table->id\(\);)/', $content, $matches)) {
            $enhancedFields =
                "            \$table->id();\n".
                "            \$table->string('first_name');\n".
                "            \$table->string('last_name');\n".
                "            \$table->string('email')->unique();\n".
                "            \$table->string('mobile_number')->nullable();\n".
                "            \$table->timestamp('email_verified_at')->nullable();\n".
                "            \$table->string('password');\n".
                "            \$table->rememberToken();\n";

            // Replace id() with enhanced fields, then look for timestamps
            $content = preg_replace('/\$table->id\(\);/', $enhancedFields, $content, 1);

            // Remove duplicate timestamps if they exist
            $content = preg_replace('/\$table->timestamps\(\);\s*\$table->timestamps\(\);/', '$table->timestamps();', $content);
        } else {
            // Fallback: try to find where to insert before timestamps
            $pattern = '/(function\s*\(Blueprint\s+\$table\)\s*\{[\s\S]*?)(\$table->timestamps\(\);)/';
            $replacement = '$1'.
                "            \$table->string('first_name');\n".
                "            \$table->string('last_name');\n".
                "            \$table->string('email')->unique();\n".
                "            \$table->string('mobile_number')->nullable();\n".
                "            \$table->timestamp('email_verified_at')->nullable();\n".
                "            \$table->string('password');\n".
                "            \$table->rememberToken();\n".
                '            $2';

            $content = preg_replace($pattern, $replacement, $content);
        }

        File::put($migrationFile, $content);
        $this->info('   ✅ Added enhanced fields to User migration.');
    }

    /**
     * Update User model with enhanced fields in fillable
     */
    protected function updateUserModel(): void
    {
        $modelPath = app_path('Models/User.php');
        if (! File::exists($modelPath)) {
            // Try alternative location
            $modelPath = app_path('User.php');
            if (! File::exists($modelPath)) {
                return;
            }
        }

        $content = File::get($modelPath);

        // Check if fields already exist
        if (strpos($content, 'first_name') !== false) {
            $this->comment('   ℹ️  Enhanced fields already exist in User model.');

            return;
        }

        // Update fillable array
        $pattern = "/(protected\s+\$fillable\s*=\s*\[)([^\]]*)(\];)/s";
        $replacement = '$1'.
            "\n        'first_name',\n".
            "        'last_name',\n".
            "        'email',\n".
            "        'mobile_number',\n".
            "        'password',\n".
            "        'email_verified_at',\n".
            '    $3';

        $content = preg_replace($pattern, $replacement, $content);

        File::put($modelPath, $content);
        $this->info('   ✅ Added enhanced fields to User model.');
    }

    /**
     * Update User Resource with enhanced columns and form fields
     */
    protected function updateUserResource(): void
    {
        $resourcePath = app_path('Support/Inertia/Resources/UserResource.php');
        if (! File::exists($resourcePath)) {
            return;
        }

        $content = File::get($resourcePath);

        // Check if enhanced fields already exist
        if (strpos($content, 'first_name') !== false) {
            $this->comment('   ℹ️  Enhanced fields already exist in User Resource.');

            return;
        }

        // Add imports if needed
        if (strpos($content, 'use InertiaResource\\FormFields\\TextField;') === false) {
            $content = str_replace(
                'use InertiaResource\\Columns\\TextColumn;',
                "use InertiaResource\\Columns\\TextColumn;\nuse InertiaResource\\FormFields\\TextField;",
                $content
            );
        }

        // Update columns in table() method - replace 'name' with enhanced fields
        // The User resource has: id, name, email by default
        // Replace the 'name' column with first_name, last_name, and add mobile_number after email
        if (strpos($content, "TextColumn::make('name', 'Name')") !== false) {
            // Replace name with first_name and last_name
            $content = str_replace(
                "                TextColumn::make('name', 'Name'),",
                "                TextColumn::make('first_name', 'First Name'),\n                TextColumn::make('last_name', 'Last Name'),",
                $content
            );
            // Add mobile_number after email
            $content = str_replace(
                "                TextColumn::make('email', 'EMAIL'),",
                "                TextColumn::make('email', 'Email'),\n                TextColumn::make('mobile_number', 'Mobile Number'),",
                $content
            );
        } else {
            // Fallback: add enhanced fields after email
            $columnsPattern = "/(TextColumn::make\('email', '[^']+'\),)/";
            $columnsReplacement = '$1'.
                "\n                TextColumn::make('first_name', 'First Name'),\n".
                "                TextColumn::make('last_name', 'Last Name'),\n".
                "                TextColumn::make('mobile_number', 'Mobile Number'),";
            $content = preg_replace($columnsPattern, $columnsReplacement, $content);
        }

        // Update form fields in form() method
        $formPattern = "/(\/\/ Add your form fields here[\s\S]*?)(\]\s*;)/";
        if (preg_match($formPattern, $content)) {
            $formReplacement = '$1'.
                "\n            TextField::make('first_name', 'First Name')->required(),\n".
                "            TextField::make('last_name', 'Last Name')->required(),\n".
                "            TextField::make('email', 'Email')->type('email')->required(),\n".
                "            TextField::make('mobile_number', 'Mobile Number'),\n".
                "            TextField::make('password', 'Password')->type('password'),\n".
                '        $2';
            $content = preg_replace($formPattern, $formReplacement, $content);
        } else {
            // If no placeholder, add fields before the closing bracket
            $formPattern2 = "/(return\s+\[)([\s\S]*?)(\]\s*;\s*}\s*public static function)/";
            $formReplacement2 = '$1'.
                "\n            TextField::make('first_name', 'First Name')->required(),\n".
                "            TextField::make('last_name', 'Last Name')->required(),\n".
                "            TextField::make('email', 'Email')->type('email')->required(),\n".
                "            TextField::make('mobile_number', 'Mobile Number'),\n".
                "            TextField::make('password', 'Password')->type('password'),\n".
                '        $3';
            $content = preg_replace($formPattern2, $formReplacement2, $content);
        }

        File::put($resourcePath, $content);
        $this->info('   ✅ Added enhanced fields to User Resource.');
    }

    /**
     * Create Cursor and Laravel Boost rules files
     *
     * @param  bool  $forceOverwrite  Whether to overwrite existing files
     */
    protected function createCursorRules(bool $forceOverwrite = false): void
    {
        $basePath = base_path();
        $cursorRulesPath = $basePath.'/.cursorrules';
        $cursorDir = $basePath.'/.cursor';
        $cursorRulesJsonPath = $cursorDir.'/rules.json';

        // Create .cursor directory if it doesn't exist
        if (! File::exists($cursorDir)) {
            File::makeDirectory($cursorDir, 0755, true);
        }

        // Rules content for .cursorrules (plain text format for Cursor)
        $cursorRulesContent = <<<'RULES'
## InertiaResource System

This application uses the InertiaResource package to generate CRUD interfaces. When creating new resources, follow the established patterns exactly.

### Resource Class Structure
- Resource classes must extend `InertiaResource\Inertia\InertiaResource`
- Location: `app/Support/Inertia/Resources/{ResourceName}/{ResourceName}Resource.php`
- Required static properties:
  - `$model`: Fully qualified model class (e.g., `\App\Models\Customer::class`)
  - `$title`: Display title (e.g., `'Customer'`)
  - `$slug`: URL slug (e.g., `'customers'`) - use kebab-case, plural
  - `$indexPage`: Vue page path (e.g., `'Resources/Customer/Index'`)
  - `$createPage`: Vue page path (e.g., `'Resources/Customer/Create'`)
  - `$editPage`: Vue page path (e.g., `'Resources/Customer/Edit'`)
  - `$showPage`: Vue page path (e.g., `'Resources/Customer/Show'`)

### Table Configuration
- The `table()` method must return an array with:
  - `columns`: Array of column definitions using column classes
  - `filters`: Array of filter definitions (can be empty)
  - `actions`: Array of action definitions (e.g., `[['name' => 'view', 'label' => 'View']]`)
  - `bulkActions`: Array of bulk action definitions (can be empty)

### Form Configuration
- The `form()` method must return an array of form field definitions
- Use field classes from `InertiaResource\FormFields\*`
- Common field types: `TextField`, `DateField`, `NumberField`, `RelationshipField`
- Chain methods like `->required()`, `->step(0.01)` for NumberField

### Available Column Types
- `TextColumn::make('field_name', 'Label')` - For text fields
- `TextColumn::make('relationship.field', 'Label')` - For relationship fields (e.g., `'site.name'`)
- `DateColumn::make('date', 'Date')` - For date fields

### Available Form Field Types
- `TextField::make('name', 'Label')->required()` - Text input
- `DateField::make('date', 'Date')->required()` - Date input
- `NumberField::make('amount', 'Amount')->step(0.01)` - Number input
- `RelationshipField::make('site_id', 'Site')->relationship('site', 'name')` - Select dropdown for relationships

### Controller Structure
- Controllers must extend `InertiaResource\Http\Controllers\BaseResourceController`
- Location: `app/Http/Controllers/Inertia/{ResourceName}/{ResourceName}Controller.php`
- Required methods:
  - `getResourceClass()`: Return the resource class (e.g., `CustomerResource::class`)
  - `getModel()`: Return the model class (e.g., `Customer::class`)
  - `getIndexRoute()`: Return the route name (e.g., `'vue.customers.index'`)
- Optional: Override `getQuery()` to eager load relationships

### Route Naming Convention
- Route prefix: Use the resource slug (kebab-case, plural)
- Route name pattern: `{resource-slug}.{action}` (e.g., `customers.index`, `rainfall-datas.create`)
- Standard CRUD routes: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- Additional routes: `bulk-action`, `export`
- Route group structure:
  ```php
  Route::prefix('{resource-slug}')->name('{resource-slug}.')->group(function () {
      Route::get('/', [Controller::class, 'index'])->name('index');
      // ... other routes
  });
  ```

### Vue Page Structure
- Location: `resources/js/Pages/Resources/{ResourceName}/`
- Required pages: `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`
- All pages receive props: `fields`, `resourceSlug`, `title`
- Index page also receives: `data`, `columns`, `actions`, `bulkActions`, `filters`, `customFilters`, `filterValues`, `presetViews`, `activePreset`, `activePresets`, `allColumns`, `rawSql`, `description`
- Create/Edit pages also receive: `item` (Edit only)
- Show page also receives: `item`

### Index Page Pattern
- Uses `BaseDataTable` component with all required props
- Uses `Pagination` component if `data.links` exists
- Implements handlers: `handleAction`, `handleBulkAction`, `handleSort`, `handleFilter`
- Route names in handlers: `route('{resource-slug}.{action}', id)`
- Action handler switch cases: `'view'`, `'edit'`, `'delete'`
- Bulk action handler switch cases: `'delete'`, `'export'`
- Uses `usePreserveQueryParams` composable for sorting
- Header actions template slot with "Add {title}" link to create route

### Create Page Pattern
- Uses `useForm` from Inertia
- Dynamically renders form fields using `getFieldComponent` helper
- Field component mapping includes: `text`, `email`, `select`, `textarea`, `date`, `datetime`, `number`, `toggle`, `checkbox`, `multi-select`, `file`, `file-upload`
- Form submission: `form.post(route('{resource-slug}.store'), { preserveScroll: true })`
- Cancel link: `route('{resource-slug}.index')`

### Edit Page Pattern
- Same as Create page but:
  - Receives `item` prop
  - Initializes form with `props.item[field.name]` values
  - Uses `getItemId` helper to safely extract ID
  - Form submission: `form.put(route('{resource-slug}.update', itemId), { preserveScroll: true })`

### Show Page Pattern
- Uses `Card` and `Badge` components
- Displays fields in a grid layout
- Handles special field types: `boolean` (Badge), `date` (formatted), `money` (formatted), `textarea` (whitespace-pre-wrap)
- Edit button: `route('{resource-slug}.edit', itemId)`
- Delete button: `router.delete(route('{resource-slug}.destroy', itemId))`
- Uses `getItemId` helper and `computed` for itemId

### Helper Functions
- `getItemId(item)`: Safely extracts ID from item object, checks: `id`, `ID`, `Id`, `uuid`, `UUID`
- `getFieldComponent(type)`: Maps field types to Vue components
- `formatDate(date)`: Formats date using `toLocaleDateString()`
- `formatMoney(amount)`: Formats as South African Rand (R) with commas

### Importing Components
- Index: `BaseDataTable`, `Pagination`, `usePreserveQueryParams`, `Authenticated`
- Create/Edit: `useForm`, `Link`, all form field components
- Show: `Card`, `Badge`, `router`, `Link`, `computed`

### Route Helper Usage
- Always use `route()` helper function, not hardcoded URLs
- Pattern: `route('{resource-slug}.{action}', id)` where id is optional
- Example: `route('customers.show', row.id)`

### Naming Conventions
- Resource class: `{ModelName}Resource` (e.g., `CustomerResource`)
- Controller: `{ModelName}Controller` (e.g., `CustomerController`)
- Route slug: kebab-case, plural (e.g., `customers`, `rainfall-datas`)
- Vue page directory: PascalCase matching model name (e.g., `Customer`, `RainfallData`)

### When Creating New Resources
1. Create the Resource class in `app/Support/Inertia/Resources/{ResourceName}/`
2. Create the Controller in `app/Http/Controllers/Inertia/{ResourceName}/`
3. Create Vue pages in `resources/js/Pages/Resources/{ResourceName}/`
4. Add routes to `routes/web.php` following the established pattern
5. Copy the structure from existing resources (Customer or RainfallData) and adapt
6. Ensure all route names use the resource slug consistently
7. Ensure all Vue pages use the same prop structure and handlers
RULES;

        // JSON rules for Laravel Boost (.cursor/rules.json)
        $cursorRulesJson = [
            'agents' => ['cursor'],
            'editors' => ['cursor'],
            'guidelines' => [
                [
                    'name' => 'inertia-resource/core',
                    'rules' => [
                        '## InertiaResource System',
                        '',
                        'This application uses the InertiaResource package to generate CRUD interfaces. When creating new resources, follow the established patterns exactly.',
                        '',
                        '### Resource Class Structure',
                        '- Resource classes must extend `InertiaResource\\Inertia\\InertiaResource`',
                        '- Location: `app/Support/Inertia/Resources/{ResourceName}/{ResourceName}Resource.php`',
                        '- Required static properties:',
                        '  - `$model`: Fully qualified model class (e.g., `\\App\\Models\\Customer::class`)',
                        '  - `$title`: Display title (e.g., `\'Customer\'`)',
                        '  - `$slug`: URL slug (e.g., `\'customers\'`) - use kebab-case, plural',
                        '  - `$indexPage`: Vue page path (e.g., `\'Resources/Customer/Index\'`)',
                        '  - `$createPage`: Vue page path (e.g., `\'Resources/Customer/Create\'`)',
                        '  - `$editPage`: Vue page path (e.g., `\'Resources/Customer/Edit\'`)',
                        '  - `$showPage`: Vue page path (e.g., `\'Resources/Customer/Show\'`)',
                        '',
                        '### Table Configuration',
                        '- The `table()` method must return an array with:',
                        '  - `columns`: Array of column definitions using column classes',
                        '  - `filters`: Array of filter definitions (can be empty)',
                        '  - `actions`: Array of action definitions (e.g., `[[\'name\' => \'view\', \'label\' => \'View\']]`)',
                        '  - `bulkActions`: Array of bulk action definitions (can be empty)',
                        '',
                        '### Form Configuration',
                        '- The `form()` method must return an array of form field definitions',
                        '- Use field classes from `InertiaResource\\FormFields\\*`',
                        '- Common field types: `TextField`, `DateField`, `NumberField`, `RelationshipField`',
                        '- Chain methods like `->required()`, `->step(0.01)` for NumberField',
                        '',
                        '### Available Column Types',
                        '- `TextColumn::make(\'field_name\', \'Label\')` - For text fields',
                        '- `TextColumn::make(\'relationship.field\', \'Label\')` - For relationship fields (e.g., `\'site.name\'`)',
                        '- `DateColumn::make(\'date\', \'Date\')` - For date fields',
                        '',
                        '### Available Form Field Types',
                        '- `TextField::make(\'name\', \'Label\')->required()` - Text input',
                        '- `DateField::make(\'date\', \'Date\')->required()` - Date input',
                        '- `NumberField::make(\'amount\', \'Amount\')->step(0.01)` - Number input',
                        '- `RelationshipField::make(\'site_id\', \'Site\')->relationship(\'site\', \'name\')` - Select dropdown for relationships',
                        '',
                        '### Controller Structure',
                        '- Controllers must extend `InertiaResource\\Http\\Controllers\\BaseResourceController`',
                        '- Location: `app/Http/Controllers/Inertia/{ResourceName}/{ResourceName}Controller.php`',
                        '- Required methods:',
                        '  - `getResourceClass()`: Return the resource class (e.g., `CustomerResource::class`)',
                        '  - `getModel()`: Return the model class (e.g., `Customer::class`)',
                        '  - `getIndexRoute()`: Return the route name (e.g., `\'vue.customers.index\'`)',
                        '- Optional: Override `getQuery()` to eager load relationships',
                        '',
                        '### Route Naming Convention',
                        '- Route prefix: Use the resource slug (kebab-case, plural)',
                        '- Route name pattern: `{resource-slug}.{action}` (e.g., `customers.index`, `rainfall-datas.create`)',
                        '- Standard CRUD routes: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`',
                        '- Additional routes: `bulk-action`, `export`',
                        '- Route group structure:',
                        '  ```php',
                        '  Route::prefix(\'{resource-slug}\')->name(\'{resource-slug}.\')->group(function () {',
                        '      Route::get(\'/\', [Controller::class, \'index\'])->name(\'index\');',
                        '      // ... other routes',
                        '  });',
                        '  ```',
                        '',
                        '### Vue Page Structure',
                        '- Location: `resources/js/Pages/Resources/{ResourceName}/`',
                        '- Required pages: `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`',
                        '- All pages receive props: `fields`, `resourceSlug`, `title`',
                        '- Index page also receives: `data`, `columns`, `actions`, `bulkActions`, `filters`, `customFilters`, `filterValues`, `presetViews`, `activePreset`, `activePresets`, `allColumns`, `rawSql`, `description`',
                        '- Create/Edit pages also receive: `item` (Edit only)',
                        '- Show page also receives: `item`',
                        '',
                        '### Index Page Pattern',
                        '- Uses `BaseDataTable` component with all required props',
                        '- Uses `Pagination` component if `data.links` exists',
                        '- Implements handlers: `handleAction`, `handleBulkAction`, `handleSort`, `handleFilter`',
                        '- Route names in handlers: `route(\'{resource-slug}.{action}\', id)`',
                        '- Action handler switch cases: `\'view\'`, `\'edit\'`, `\'delete\'`',
                        '- Bulk action handler switch cases: `\'delete\'`, `\'export\'`',
                        '- Uses `usePreserveQueryParams` composable for sorting',
                        '- Header actions template slot with "Add {title}" link to create route',
                        '',
                        '### Create Page Pattern',
                        '- Uses `useForm` from Inertia',
                        '- Dynamically renders form fields using `getFieldComponent` helper',
                        '- Field component mapping includes: `text`, `email`, `select`, `textarea`, `date`, `datetime`, `number`, `toggle`, `checkbox`, `multi-select`, `file`, `file-upload`',
                        '- Form submission: `form.post(route(\'{resource-slug}.store\'), { preserveScroll: true })`',
                        '- Cancel link: `route(\'{resource-slug}.index\')`',
                        '',
                        '### Edit Page Pattern',
                        '- Same as Create page but:',
                        '  - Receives `item` prop',
                        '  - Initializes form with `props.item[field.name]` values',
                        '  - Uses `getItemId` helper to safely extract ID',
                        '  - Form submission: `form.put(route(\'{resource-slug}.update\', itemId), { preserveScroll: true })`',
                        '',
                        '### Show Page Pattern',
                        '- Uses `Card` and `Badge` components',
                        '- Displays fields in a grid layout',
                        '- Handles special field types: `boolean` (Badge), `date` (formatted), `money` (formatted), `textarea` (whitespace-pre-wrap)',
                        '- Edit button: `route(\'{resource-slug}.edit\', itemId)`',
                        '- Delete button: `router.delete(route(\'{resource-slug}.destroy\', itemId))`',
                        '- Uses `getItemId` helper and `computed` for itemId',
                        '',
                        '### Helper Functions',
                        '- `getItemId(item)`: Safely extracts ID from item object, checks: `id`, `ID`, `Id`, `uuid`, `UUID`',
                        '- `getFieldComponent(type)`: Maps field types to Vue components',
                        '- `formatDate(date)`: Formats date using `toLocaleDateString()`',
                        '- `formatMoney(amount)`: Formats as South African Rand (R) with commas',
                        '',
                        '### Importing Components',
                        '- Index: `BaseDataTable`, `Pagination`, `usePreserveQueryParams`, `Authenticated`',
                        '- Create/Edit: `useForm`, `Link`, all form field components',
                        '- Show: `Card`, `Badge`, `router`, `Link`, `computed`',
                        '',
                        '### Route Helper Usage',
                        '- Always use `route()` helper function, not hardcoded URLs',
                        '- Pattern: `route(\'{resource-slug}.{action}\', id)` where id is optional',
                        '- Example: `route(\'customers.show\', row.id)`',
                        '',
                        '### Naming Conventions',
                        '- Resource class: `{ModelName}Resource` (e.g., `CustomerResource`)',
                        '- Controller: `{ModelName}Controller` (e.g., `CustomerController`)',
                        '- Route slug: kebab-case, plural (e.g., `customers`, `rainfall-datas`)',
                        '- Vue page directory: PascalCase matching model name (e.g., `Customer`, `RainfallData`)',
                        '',
                        '### When Creating New Resources',
                        '1. Create the Resource class in `app/Support/Inertia/Resources/{ResourceName}/`',
                        '2. Create the Controller in `app/Http/Controllers/Inertia/{ResourceName}/`',
                        '3. Create Vue pages in `resources/js/Pages/Resources/{ResourceName}/`',
                        '4. Add routes to `routes/web.php` following the established pattern',
                        '5. Copy the structure from existing resources (Customer or RainfallData) and adapt',
                        '6. Ensure all route names use the resource slug consistently',
                        '7. Ensure all Vue pages use the same prop structure and handlers',
                    ],
                ],
            ],
            'herd_mcp' => true,
        ];

        // Write .cursorrules file (for Cursor)
        $cursorRulesExists = File::exists($cursorRulesPath);
        if ($cursorRulesExists && ! $forceOverwrite) {
            $this->comment('   ℹ️  .cursorrules already exists. Skipping...');
        } else {
            File::put($cursorRulesPath, $cursorRulesContent);
            $this->info('   ✅ '.($forceOverwrite && $cursorRulesExists ? 'Updated' : 'Created').' .cursorrules file');
        }

        // Write .cursor/rules.json file (for Laravel Boost)
        $cursorRulesJsonExists = File::exists($cursorRulesJsonPath);
        if ($cursorRulesJsonExists && ! $forceOverwrite) {
            $this->comment('   ℹ️  .cursor/rules.json already exists. Skipping...');
        } else {
            File::put($cursorRulesJsonPath, json_encode($cursorRulesJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('   ✅ '.($forceOverwrite && $cursorRulesJsonExists ? 'Updated' : 'Created').' .cursor/rules.json file');
        }
    }
}
