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
    protected $signature = 'vue-admin-panel:force-reinstall {--fresh : Run fresh migrations (drop all tables)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force reinstall Vue Admin Panel with overwrites and minimal prompts';

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
        $this->info('🚀 Force Reinstalling Vue Admin Panel...');
        $this->newLine();

        // Check if --fresh flag is provided
        $this->runFreshMigrations = $this->option('fresh');
        
        // Ask about fresh migrations if --fresh not provided
        if (!$this->runFreshMigrations) {
            $this->runFreshMigrations = $this->confirm('Do you want to run fresh migrations (drop all tables)?', false);
        }

        // Track which resources were created
        $userResourceCreated = false;
        $customerResourceCreated = false;
        
        // Use default for Customers (false) - no prompt
        $useCustomers = false;
        $this->updateCustomersConfig($useCustomers);
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

        // Create login pages (force overwrite)
        $this->info('🔐 Creating login pages...');
        $this->createLoginPages();
        $this->newLine();

        // Create admin routes (force overwrite)
        $this->info('🛣️  Creating admin routes...');
        $this->createAdminRoutes();
        $this->newLine();

        // Create customer routes (if enabled)
        $useCustomers = config('inertia-resource.use_customers', false);
        if ($useCustomers) {
            $this->info('🛣️  Creating customer routes...');
            $this->createCustomerRoutes();
            $this->newLine();
        }

        // Create admin layouts and dashboard (force overwrite)
        $this->info('📐 Creating admin layouts and dashboard...');
        $this->createAdminLayouts();
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
        $customerResourceCreated = false;
        
        // Check User model and migration (use defaults)
        $this->info('👤 Checking User model and migration...');
        $userModelExists = $this->checkUserModelExists();
        $userMigrationExists = $this->checkMigrationExists('create_users_table');
        
        if (!$userModelExists) {
            $this->warn('⚠️  User model not found.');
            // Use default: yes
            $this->createUserModel();
        } else {
            $this->comment('ℹ️  User model already exists.');
        }
        
        if (!$userMigrationExists) {
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
        if (!class_exists($userModel)) {
            // Try alternative namespace
            $userModel = 'App\\User';
            if (!class_exists($userModel)) {
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

        // Create Customer Resource if customers are enabled (use default: yes)
        if ($this->shouldCreateCustomerResource && $this->customerModel) {
            $this->newLine();
            $this->info('📦 Creating Customer Resource...');
            
            // Refresh autoloader before checking if Customer model exists
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            // Give autoloader a moment to catch up
            usleep(500000); // 0.5 seconds
            
            // Check if Customer model exists
            if (class_exists($this->customerModel)) {
                $this->call('make:inertia-resource', [
                    'model' => $this->customerModel,
                    '--all' => true,
                ]);
                $this->newLine();
                $customerResourceCreated = true;
            } else {
                $this->warn("⚠️  Customer model '{$this->customerModel}' not found. Skipping Customer Resource creation.");
                $this->comment("   Please create the Customer Resource manually: php artisan make:inertia-resource \"{$this->customerModel}\" --all");
                $this->newLine();
            }
        }

        // Use default: no for Menu Groups and Items
        // Skip this step in force reinstall
        
        // Create and run ResourceMenuSeeder if any resources were created
        if ($userResourceCreated || $customerResourceCreated) {
            $this->createAndRunResourceMenuSeeder();
        }
        
        $this->newLine();
        $this->info('✅ Vue Admin Panel force reinstall complete!');
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
                if (!\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
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
                $this->warn('⚠️  Migration error: ' . $e->getMessage());
                $this->comment('   You may need to run migrations manually: php artisan migrate');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Migration error: ' . $e->getMessage());
            $this->comment('   You may need to run migrations manually: php artisan migrate');
        }
    }

    /**
     * Update customers configuration with defaults (no prompts)
     */
    protected function updateCustomersConfig(bool $useCustomers): void
    {
        $configPath = config_path('inertia-resource.php');
        $packageConfigPath = __DIR__.'/../../config/inertia-resource.php';

        // If config file doesn't exist, copy it from the package
        if (!File::exists($configPath)) {
            if (File::exists($packageConfigPath)) {
                // Ensure config directory exists
                $configDir = config_path();
                if (!File::exists($configDir)) {
                    File::makeDirectory($configDir, 0755, true);
                }
                File::copy($packageConfigPath, $configPath);
            } else {
                $this->warn('⚠️  Could not find package config file. Please publish config manually.');
                return;
            }
        }

        if (File::exists($configPath)) {
            $configContent = File::get($configPath);
            
            // Update the use_customers value
            $configContent = preg_replace(
                "/'use_customers'\s*=>\s*(true|false),/",
                "'use_customers' => " . ($useCustomers ? 'true' : 'false') . ',',
                $configContent
            );

            if ($useCustomers) {
                // Use default customer model name
                $defaultCustomerModel = 'App\\Models\\Customer';
                $customerModel = $defaultCustomerModel;
                
                // Extract model name and namespace
                $modelParts = explode('\\', $customerModel);
                $modelName = end($modelParts);
                $namespace = implode('\\', array_slice($modelParts, 0, -1));
                
                // Determine model path based on namespace
                if ($namespace === 'App\\Models' || $namespace === 'App') {
                    // Use standard Laravel structure
                    $modelPath = app_path('Models/' . $modelName . '.php');
                } else {
                    // Custom namespace - create in appropriate directory
                    $relativePath = str_replace('App\\', '', $namespace);
                    $relativePath = str_replace('\\', '/', $relativePath);
                    $modelPath = app_path($relativePath . '/' . $modelName . '.php');
                }
                
                // Check if model already exists
                $modelExists = class_exists($customerModel) || File::exists($modelPath);
                $migrationExists = $this->checkMigrationExists('create_' . strtolower($modelName) . 's_table');
                
                if (!$modelExists) {
                    // Create the customer model
                    $this->info('📦 Creating Customer model...');
                    $this->newLine();
                    // Create model directory if needed
                    $modelDir = dirname($modelPath);
                    if (!File::exists($modelDir)) {
                        File::makeDirectory($modelDir, 0755, true);
                    }
                    
                    // Create the model file
                    $modelStub = "<?php\n\n";
                    $modelStub .= "namespace {$namespace};\n\n";
                    $modelStub .= "use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n";
                    $modelStub .= "use Illuminate\\Foundation\\Auth\\User as Authenticatable;\n";
                    $modelStub .= "use Illuminate\\Notifications\\Notifiable;\n\n";
                    $modelStub .= "class {$modelName} extends Authenticatable\n";
                    $modelStub .= "{\n";
                    $modelStub .= "    use HasFactory, Notifiable;\n\n";
                    $modelStub .= "    /**\n";
                    $modelStub .= "     * The attributes that are mass assignable.\n";
                    $modelStub .= "     *\n";
                    $modelStub .= "     * @var array<int, string>\n";
                    $modelStub .= "     */\n";
                    $modelStub .= "    protected \$fillable = [\n";
                    $modelStub .= "        'name',\n";
                    $modelStub .= "        'email',\n";
                    $modelStub .= "        'password',\n";
                    $modelStub .= "    ];\n\n";
                    $modelStub .= "    /**\n";
                    $modelStub .= "     * The attributes that should be hidden for serialization.\n";
                    $modelStub .= "     *\n";
                    $modelStub .= "     * @var array<int, string>\n";
                    $modelStub .= "     */\n";
                    $modelStub .= "    protected \$hidden = [\n";
                    $modelStub .= "        'password',\n";
                    $modelStub .= "        'remember_token',\n";
                    $modelStub .= "    ];\n\n";
                    $modelStub .= "    /**\n";
                    $modelStub .= "     * Get the attributes that should be cast.\n";
                    $modelStub .= "     *\n";
                    $modelStub .= "     * @return array<string, string>\n";
                    $modelStub .= "     */\n";
                    $modelStub .= "    protected function casts(): array\n";
                    $modelStub .= "    {\n";
                    $modelStub .= "        return [\n";
                    $modelStub .= "            'email_verified_at' => 'datetime',\n";
                    $modelStub .= "            'password' => 'hashed',\n";
                    $modelStub .= "        ];\n";
                    $modelStub .= "    }\n";
                    $modelStub .= "}\n";
                    
                    File::put($modelPath, $modelStub);
                    $this->info("✅ Created Customer model: {$customerModel}");
                    $this->newLine();
                    
                    // Clear autoloader cache so the new class can be found
                    if (function_exists('opcache_reset')) {
                        opcache_reset();
                    }
                    // Give autoloader a moment to catch up
                    usleep(500000); // 0.5 seconds
                }
                
                // Check and create migration if needed
                if (!$migrationExists) {
                    $this->info('📦 Creating Customer migration...');
                    $this->createCustomerMigration($modelName);
                } else {
                    $this->comment("ℹ️  Customer migration already exists.");
                }
                
                // Update config with customer model
                $configContent = preg_replace(
                    "/'customer_model'\s*=>\s*null,/",
                    "'customer_model' => \\{$customerModel}::class,",
                    $configContent
                );
                
                // Store customer model and use default: yes for Customer Resource
                $this->customerModel = $customerModel;
                $this->shouldCreateCustomerResource = true;
                
                // Configure customer guard in auth.php
                $this->configureCustomerGuard($customerModel);
            }

            File::put($configPath, $configContent);
            
            if ($useCustomers) {
                $this->info('✅ Enabled Customers in configuration.');
            } else {
                $this->info('✅ Disabled Customers in configuration.');
            }
        } else {
            $this->warn('⚠️  Could not update config/inertia-resource.php. Please set use_customers manually.');
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
        if (!File::exists($viewsPath)) {
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
        if (!File::exists($jsPath)) {
            File::makeDirectory($jsPath, 0755, true);
        }

        // Create css directory if it doesn't exist
        if (!File::exists($cssPath)) {
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
     * Create login pages with force overwrite
     */
    protected function createLoginPages(): void
    {
        $pagesPath = resource_path('js/Pages');
        $authPath = $pagesPath.'/Auth';

        // Create Auth directory if it doesn't exist
        if (!File::exists($authPath)) {
            File::makeDirectory($authPath, 0755, true);
        }

        // Admin Login Page (force overwrite from vendor)
        $vendorPath = resource_path('js/vendor/inertia-resource');
        $adminLoginSource = $vendorPath.'/Pages/Auth/AdminLogin.vue';
        $adminLoginPath = $authPath.'/AdminLogin.vue';

        if (File::exists($adminLoginSource)) {
            File::copy($adminLoginSource, $adminLoginPath);
            $this->info('Created/Updated AdminLogin.vue (from vendor)');
        } else {
            $this->warn('⚠️  AdminLogin.vue not found in vendor. Make sure to publish assets first.');
        }

        // Customer Login Page (force overwrite from vendor, only if use_customers is enabled)
        $useCustomers = config('inertia-resource.use_customers', false);
        
        if ($useCustomers) {
            $customerLoginSource = $vendorPath.'/Pages/Auth/CustomerLogin.vue';
            $customerLoginPath = $authPath.'/CustomerLogin.vue';

            if (File::exists($customerLoginSource)) {
                File::copy($customerLoginSource, $customerLoginPath);
                $this->info('Created/Updated CustomerLogin.vue (from vendor)');
            } else {
                $this->warn('⚠️  CustomerLogin.vue not found in vendor. Make sure to publish assets first.');
            }
        } else {
            $this->comment('Customer login page skipped (use_customers is disabled).');
        }
    }

    /**
     * Create admin layouts and dashboard with force overwrite (from vendor)
     */
    protected function createAdminLayouts(): void
    {
        $layoutsPath = resource_path('js/Layouts');
        $pagesPath = resource_path('js/Pages');
        $componentsPath = resource_path('js/Components');
        $vendorPath = resource_path('js/vendor/inertia-resource');

        // Create directories if they don't exist
        if (!File::exists($layoutsPath)) {
            File::makeDirectory($layoutsPath, 0755, true);
        }

        if (!File::exists($pagesPath)) {
            File::makeDirectory($pagesPath, 0755, true);
        }

        $dashboardComponentsPath = $componentsPath.'/Dashboard';
        if (!File::exists($dashboardComponentsPath)) {
            File::makeDirectory($dashboardComponentsPath, 0755, true);
        }

        // AdminLayout (force overwrite from vendor)
        $adminLayoutSource = $vendorPath.'/Layouts/AdminLayout.vue';
        $adminLayoutPath = $layoutsPath.'/AdminLayout.vue';
        if (File::exists($adminLayoutSource)) {
            File::copy($adminLayoutSource, $adminLayoutPath);
            $this->info('Created/Updated AdminLayout.vue (from vendor)');
        } else {
            $this->warn('⚠️  AdminLayout.vue not found in vendor. Make sure to publish assets first.');
        }

        // DashboardLayout (force overwrite from vendor)
        $dashboardLayoutSource = $vendorPath.'/Layouts/DashboardLayout.vue';
        $dashboardLayoutPath = $layoutsPath.'/DashboardLayout.vue';
        if (File::exists($dashboardLayoutSource)) {
            File::copy($dashboardLayoutSource, $dashboardLayoutPath);
            $this->info('Created/Updated DashboardLayout.vue (from vendor)');
        }

        // Dashboard Page (force overwrite from vendor)
        $dashboardSource = $vendorPath.'/Pages/Dashboard.vue';
        $dashboardPath = $pagesPath.'/Dashboard.vue';
        if (File::exists($dashboardSource)) {
            File::copy($dashboardSource, $dashboardPath);
            $this->info('Created/Updated Dashboard.vue (from vendor)');
        } else {
            $this->warn('⚠️  Dashboard.vue not found in vendor. Make sure to publish assets first.');
        }

        // StatCard Component (force overwrite from vendor)
        $statCardSource = $vendorPath.'/Components/Dashboard/StatCard.vue';
        $statCardPath = $dashboardComponentsPath.'/StatCard.vue';
        if (File::exists($statCardSource)) {
            File::copy($statCardSource, $statCardPath);
            $this->info('Created/Updated StatCard.vue (from vendor)');
        }
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
        if (!File::exists($uiPath)) {
            File::makeDirectory($uiPath, 0755, true);
        }
        if (!File::exists($composablesPath)) {
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
