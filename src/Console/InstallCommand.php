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
    protected $signature = 'vue-admin-panel:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Vue Admin Panel npm dependencies and configuration';

    /**
     * Customer model class name (if customers are enabled)
     *
     * @var string|null
     */
    protected $customerModel = null;

    /**
     * Whether to create CustomerResource (deferred until after admin routes are created)
     *
     * @var bool
     */
    protected $shouldCreateCustomerResource = false;

    /**
     * Whether to add enhanced fields (first_name, last_name, email, mobile_number) to User and Customer models
     *
     * @var bool
     */
    protected $useEnhancedFields = false;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Installing Vue Admin Panel...');
        $this->newLine();

        // Track which resources were created
        $userResourceCreated = false;
        $customerResourceCreated = false;
        
        // Ask if user wants to use Customers
        $useCustomers = $this->confirm('Do you want to use Customers?', false);
        $this->updateCustomersConfig($useCustomers);
        $this->newLine();

        // Ask if user wants enhanced fields for User and Customer models
        $this->info('📋 Enhanced Fields Option');
        $this->comment('   You can add the following fields to User and Customer models:');
        $this->comment('   - first_name');
        $this->comment('   - last_name');
        $this->comment('   - email');
        $this->comment('   - mobile_number');
        $this->newLine();
        $this->useEnhancedFields = $this->confirm('Do you want to add these enhanced fields to User and Customer models?', false);
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

        // Login pages are now in vendor - users can override by creating files in resources/js/Pages/Auth/
        $this->info('🔐 Login pages are available in vendor...');
        $this->comment('   💡 To customize, create files in resources/js/Pages/Auth/ (they will override vendor versions)');
        $this->newLine();

        // Create admin routes
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

        // Layouts, dashboard, and components are now in vendor - users can override by creating files in resources/js/
        $this->info('📐 Layouts, dashboard, and components are available in vendor...');
        $this->comment('   💡 To customize, create files in resources/js/ (they will override vendor versions)');
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
        
        // Check User model and migration
        $this->info('👤 Checking User model and migration...');
        $userModelExists = $this->checkUserModelExists();
        $userMigrationExists = $this->checkMigrationExists('create_users_table');
        
        if (!$userModelExists) {
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
        
        if (!$userMigrationExists) {
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
                if ($this->useEnhancedFields) {
                    $this->updateUserResource();
                }
            }
        }

        // Create Customer Resource if customers are enabled and requested (after admin routes exist)
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
                if ($this->useEnhancedFields) {
                    $this->updateCustomerResource($this->customerModel);
                }
            } else {
                $this->warn("⚠️  Customer model '{$this->customerModel}' not found. Skipping Customer Resource creation.");
                $this->comment("   Please create the Customer Resource manually: php artisan make:inertia-resource \"{$this->customerModel}\" --all");
                $this->newLine();
            }
        }

        // Ask if user wants to create Menu Groups and Items
        if ($this->confirm('Create Menu Groups and Items?', false)) {
            $this->info('📦 Creating Menu Groups and Items...');
            $this->newLine();
            $this->createMenuSystem();
            $this->newLine();
        }
        
        // Create and run ResourceMenuSeeder if any resources were created
        if ($userResourceCreated || $customerResourceCreated) {
            $this->createAndRunResourceMenuSeeder();
        }
        
        $this->newLine();
        $this->info('✅ Vue Admin Panel installation complete!');
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
        $userColumnPrefsMigration = glob($migrationsPath . '/*_create_user_column_preferences_table.php');
        $menuGroupsMigration = glob($migrationsPath . '/*_create_menu_groups_table.php');
        $menuItemsMigration = glob($migrationsPath . '/*_create_menu_items_table.php');
        
        $hasAllMigrations = !empty($userColumnPrefsMigration) && 
                           !empty($menuGroupsMigration) && 
                           !empty($menuItemsMigration);
        
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
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-menu-models',
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
            if (!\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
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
                $this->warn('⚠️  Migration error: ' . $e->getMessage());
                $this->comment('   You may need to run migrations manually: php artisan migrate');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Migration error: ' . $e->getMessage());
            $this->comment('   You may need to run migrations manually: php artisan migrate');
        }
    }

    /**
     * Update customers configuration
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
                // Ask for customer model name
                $defaultCustomerModel = 'App\\Models\\Customer';
                $customerModel = $this->ask('What should the Customer model be called?', $defaultCustomerModel);
                
                // Normalize the input - if no namespace, assume App\Models
                if (strpos($customerModel, '\\') === false) {
                    $customerModel = 'App\\Models\\' . $customerModel;
                }
                
                // Validate the model class name format
                if (!preg_match('/^[A-Za-z0-9\\\\]+$/', $customerModel)) {
                    $this->error('Invalid model class name. Using default: ' . $defaultCustomerModel);
                    $customerModel = $defaultCustomerModel;
                }
                
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
                
                if ($modelExists) {
                    $this->comment("ℹ️  Customer model '{$customerModel}' already exists.");
                    // Update existing model with enhanced fields if enabled
                    if ($this->useEnhancedFields) {
                        $this->updateCustomerModel($customerModel);
                    }
                } else {
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
                    if ($this->useEnhancedFields) {
                        $modelStub .= "        'first_name',\n";
                        $modelStub .= "        'last_name',\n";
                        $modelStub .= "        'email',\n";
                        $modelStub .= "        'mobile_number',\n";
                    } else {
                        $modelStub .= "        'name',\n";
                        $modelStub .= "        'email',\n";
                        $modelStub .= "        'password',\n";
                    }
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
                    
                    // Note: Enhanced fields are already included in the model stub if enabled
                    // No need to update here since we built it with enhanced fields
                }
                
                // Check and create migration if needed
                if (!$migrationExists) {
                    $this->info('📦 Creating Customer migration...');
                    $this->createCustomerMigration($modelName);
                } else {
                    $this->comment("ℹ️  Customer migration already exists.");
                    if ($this->useEnhancedFields) {
                        $this->updateCustomerMigration($modelName);
                    }
                }
                
                // Update config with customer model
                $configContent = preg_replace(
                    "/'customer_model'\s*=>\s*null,/",
                    "'customer_model' => \\{$customerModel}::class,",
                    $configContent
                );
                
                // Store customer model and ask if user wants to create Customer Resource
                // (deferred until after admin routes are created)
                $this->customerModel = $customerModel;
                if ($this->confirm('Do you want to create a Resource for the Customer model?', true)) {
                    $this->shouldCreateCustomerResource = true;
                }
                
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
     * Configure customer guard in auth.php
     */
    protected function configureCustomerGuard(string $customerModel): void
    {
        $authConfigPath = config_path('auth.php');
        
        if (!File::exists($authConfigPath)) {
            $this->warn('⚠️  Could not find config/auth.php file. Please configure customer guard manually.');
            $this->displayGuardConfigurationInstructions($customerModel);
            return;
        }

        $authConfigContent = File::get($authConfigPath);
        $originalContent = $authConfigContent;
        
        // First, remove any incorrectly nested customer guard/provider entries
        // Use line-by-line parsing to handle nested brackets correctly
        // In Laravel config: top level = 4 spaces, nested inside guard/provider = 8 spaces
        $lines = explode("\n", $authConfigContent);
        $cleanedLines = [];
        $skipping = false;
        $skipIndentLevel = null;
        $braceDepth = 0;
        $insideGuardsOrProviders = false;
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $lineIndent = strlen($line) - strlen(ltrim($line));
            
            // Track if we're inside 'guards' or 'providers' arrays
            if (preg_match("/^\s*['\"]guards['\"]?\s*=>\s*\[/", $line) || 
                preg_match("/^\s*['\"]providers['\"]?\s*=>\s*\[/", $line)) {
                $insideGuardsOrProviders = true;
            }
            
            // Check if we've exited the guards/providers array (closing bracket at base indent)
            if ($insideGuardsOrProviders && preg_match("/^\s+\],/", $line) && $lineIndent <= 4) {
                $insideGuardsOrProviders = false;
            }
            
            // Check if this line starts a nested customer guard/provider
            // Should be at 4 spaces (top level), but if it's at 8+ spaces inside guards/providers, it's nested incorrectly
            if (!$skipping && $insideGuardsOrProviders && $lineIndent >= 8 && 
                (preg_match("/^\s+['\"]customer['\"]?\s*=>\s*\[/", $line) || 
                 preg_match("/^\s+['\"]customers['\"]?\s*=>\s*\[/", $line))) {
                // Start skipping - track the indent level we need to get back to
                $skipping = true;
                $skipIndentLevel = $lineIndent;
                $braceDepth = 1; // We just opened a bracket
                continue; // Skip this line
            }
            
            // If we're skipping, track bracket depth
            if ($skipping) {
                $braceDepth += substr_count($line, '[') - substr_count($line, ']');
                
                // Check if we've closed the nested array (braceDepth is 0 or less, and we're at or above skip indent)
                if ($braceDepth <= 0 && $lineIndent <= $skipIndentLevel) {
                    $skipping = false;
                    $skipIndentLevel = null;
                    $braceDepth = 0;
                }
                continue; // Skip this line
            }
            
            // Normal line - keep it
            $cleanedLines[] = $line;
        }
        
        $authConfigContent = implode("\n", $cleanedLines);
        
        // Check if customer guard already exists at the correct top level (after removing nested ones)
        $hasCustomerGuard = (strpos($authConfigContent, "'customer' => [") !== false || 
                            strpos($authConfigContent, '"customer" => [') !== false) &&
                           (strpos($authConfigContent, "'customers' => [") !== false || 
                            strpos($authConfigContent, '"customers" => [') !== false);
        
        if ($hasCustomerGuard) {
            // Guard exists and is correctly placed, but we may have removed incorrectly nested ones
            if ($authConfigContent !== $originalContent) {
                File::put($authConfigPath, $authConfigContent);
                $this->info('✅ Fixed incorrectly nested customer guard/provider in config/auth.php');
                $this->call('config:clear');
            } else {
                $this->comment('✅ Customer guard already correctly configured in config/auth.php');
            }
            return;
        }
        
        // Add customer provider to providers array
        $hasCustomersProvider = strpos($authConfigContent, "'customers' => [") !== false || 
                               strpos($authConfigContent, '"customers" => [') !== false;
        
        if (!$hasCustomersProvider) {
            $customersProvider = "        'customers' => [\n";
            $customersProvider .= "            'driver' => 'eloquent',\n";
            $customersProvider .= "            'model' => {$customerModel}::class,\n";
            $customersProvider .= "        ],\n";
            
            // Find the providers array - need to find the TOP-LEVEL closing bracket
            // Look for 'providers' => [ and find the matching closing bracket at the same indentation level
            $lines = explode("\n", $authConfigContent);
            $providersStartIndex = null;
            $providersEndIndex = null;
            $braceCount = 0;
            $baseIndent = null;
            
            for ($i = 0; $i < count($lines); $i++) {
                // Find the providers array start
                if (preg_match("/^(\s*)'providers'\s*=>\s*\[/", $lines[$i], $matches)) {
                    $providersStartIndex = $i;
                    $baseIndent = strlen($matches[1]);
                    $braceCount = 1;
                    continue;
                }
                
                if ($providersStartIndex !== null) {
                    // Count braces to find the closing bracket
                    $lineIndent = strlen($lines[$i]) - strlen(ltrim($lines[$i]));
                    $braceCount += substr_count($lines[$i], '[') - substr_count($lines[$i], ']');
                    
                    // Check if we've closed the providers array
                    // Must have braceCount === 0 (all nested arrays closed)
                    // AND be at the base indent level (same as 'providers' => [)
                    // AND contain the closing bracket pattern
                    if ($braceCount === 0 && $lineIndent === $baseIndent && preg_match("/^\s+\],/", $lines[$i])) {
                        $providersEndIndex = $i;
                        break;
                    }
                }
            }
            
            if ($providersStartIndex !== null && $providersEndIndex !== null) {
                // Insert before the closing bracket
                $lines[$providersEndIndex] = $customersProvider . $lines[$providersEndIndex];
                $authConfigContent = implode("\n", $lines);
            } else {
                $this->warn('⚠️  Could not find providers array closing bracket in config/auth.php. Please add manually.');
                $this->displayGuardConfigurationInstructions($customerModel);
                return;
            }
        }
        
        // Add customer guard to guards array
        $hasCustomerGuardEntry = strpos($authConfigContent, "'customer' => [") !== false || 
                                strpos($authConfigContent, '"customer" => [') !== false;
        
        if (!$hasCustomerGuardEntry) {
            $customerGuard = "        'customer' => [\n";
            $customerGuard .= "            'driver' => 'session',\n";
            $customerGuard .= "            'provider' => 'customers',\n";
            $customerGuard .= "        ],\n";
            
            // Find the guards array - need to find the TOP-LEVEL closing bracket
            $lines = explode("\n", $authConfigContent);
            $guardsStartIndex = null;
            $guardsEndIndex = null;
            $braceCount = 0;
            $baseIndent = null;
            
            for ($i = 0; $i < count($lines); $i++) {
                // Find the guards array start
                if (preg_match("/^(\s*)'guards'\s*=>\s*\[/", $lines[$i], $matches)) {
                    $guardsStartIndex = $i;
                    $baseIndent = strlen($matches[1]);
                    $braceCount = 1;
                    continue;
                }
                
                if ($guardsStartIndex !== null) {
                    // Count braces to find the closing bracket
                    $lineIndent = strlen($lines[$i]) - strlen(ltrim($lines[$i]));
                    $braceCount += substr_count($lines[$i], '[') - substr_count($lines[$i], ']');
                    
                    // Check if we've closed the guards array
                    // Must have braceCount === 0 (all nested arrays closed)
                    // AND be at the base indent level (same as 'guards' => [)
                    // AND contain the closing bracket pattern
                    if ($braceCount === 0 && $lineIndent === $baseIndent && preg_match("/^\s+\],/", $lines[$i])) {
                        $guardsEndIndex = $i;
                        break;
                    }
                }
            }
            
            if ($guardsStartIndex !== null && $guardsEndIndex !== null) {
                // Insert before the closing bracket
                $lines[$guardsEndIndex] = $customerGuard . $lines[$guardsEndIndex];
                $authConfigContent = implode("\n", $lines);
            } else {
                $this->warn('⚠️  Could not find guards array closing bracket in config/auth.php. Please add manually.');
                $this->displayGuardConfigurationInstructions($customerModel);
                return;
            }
        }
        
        // Only write if content changed
        if ($authConfigContent !== $originalContent) {
            File::put($authConfigPath, $authConfigContent);
            $this->info('✅ Configured customer guard in config/auth.php');
            
            // Verify the guard was actually added
            $updatedContent = File::get($authConfigPath);
            $guardAdded = (strpos($updatedContent, "'customer' => [") !== false || 
                          strpos($updatedContent, '"customer" => [') !== false) &&
                         (strpos($updatedContent, "'customers' => [") !== false || 
                          strpos($updatedContent, '"customers" => [') !== false);
            
            if (!$guardAdded) {
                $this->warn('⚠️  Guard configuration may not have been added correctly. Please verify config/auth.php');
                $this->displayGuardConfigurationInstructions($customerModel);
                return;
            }
            
            // Clear config cache to ensure changes are loaded
            try {
                if (\Illuminate\Support\Facades\Artisan::call('config:clear') === 0) {
                    $this->info('✅ Cleared config cache');
                }
            } catch (\Exception $e) {
                $this->comment('⚠️  Could not clear config cache. Please run: php artisan config:clear');
            }
            
            // Verify guard can be resolved (if we can test it)
            try {
                // This will only work if the app is bootstrapped
                if (app()->bound('auth')) {
                    $auth = app('auth');
                    // Try to get the guard configuration
                    $guardConfig = config('auth.guards.customer');
                    if ($guardConfig) {
                        $this->info('✅ Verified customer guard configuration');
                    }
                }
            } catch (\Exception $e) {
                // Ignore - this is just a verification step
            }
        } else {
            $this->warn('⚠️  Could not automatically configure customer guard. Please add manually.');
            $this->displayGuardConfigurationInstructions($customerModel);
        }
    }

    /**
     * Ensure customer guard is configured in auth.php
     */
    protected function ensureCustomerGuardConfigured(): void
    {
        $authConfigPath = config_path('auth.php');
        
        if (!File::exists($authConfigPath)) {
            return; // Will be handled by configureCustomerGuard if needed
        }

        $authConfigContent = File::get($authConfigPath);
        
        // Check if customer guard exists
        $hasCustomerGuard = (strpos($authConfigContent, "'customer' => [") !== false || 
                            strpos($authConfigContent, '"customer" => [') !== false) &&
                           (strpos($authConfigContent, "'customers' => [") !== false || 
                            strpos($authConfigContent, '"customers" => [') !== false);
        
        if (!$hasCustomerGuard) {
            // Get customer model from config
            $customerModel = config('inertia-resource.customer_model');
            
            if (!$customerModel) {
                // Try to get default
                $customerModel = 'App\\Models\\Customer';
            }
            
            // If it's a class constant, extract the class name
            if (is_string($customerModel) && strpos($customerModel, '::class') === false) {
                // It's already a class name string
            } elseif (is_string($customerModel)) {
                // Remove ::class if present
                $customerModel = str_replace('::class', '', $customerModel);
            } else {
                // It might be a class constant value, try to get the class name
                $customerModel = 'App\\Models\\Customer';
            }
            
            $this->info('🔧 Detected customers enabled but guard missing. Configuring customer guard...');
            $this->configureCustomerGuard($customerModel);
        }
    }

    /**
     * Display manual guard configuration instructions
     */
    protected function displayGuardConfigurationInstructions(string $customerModel): void
    {
        $this->newLine();
        $this->comment('Please add the following to your config/auth.php file:');
        $this->newLine();
        $this->line('In the "guards" array, add:');
        $this->line("        'customer' => [");
        $this->line("            'driver' => 'session',");
        $this->line("            'provider' => 'customers',");
        $this->line("        ],");
        $this->newLine();
        $this->line('In the "providers" array, add:');
        $this->line("        'customers' => [");
        $this->line("            'driver' => 'eloquent',");
        $this->line("            'model' => {$customerModel}::class,");
        $this->line("        ],");
        $this->newLine();
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
            if (!isset($existingPackageJson['dependencies'])) {
                $existingPackageJson['dependencies'] = [];
            }
            $existingPackageJson['dependencies'] = array_merge(
                $existingPackageJson['dependencies'],
                $packageDependencies
            );

            // Merge devDependencies
            if (!isset($existingPackageJson['devDependencies'])) {
                $existingPackageJson['devDependencies'] = [];
            }
            $existingPackageJson['devDependencies'] = array_merge(
                $existingPackageJson['devDependencies'],
                $packageDevDependencies
            );

            // Ensure type is module if not set
            if (!isset($existingPackageJson['type'])) {
                $existingPackageJson['type'] = 'module';
            }

            File::put($packageJsonPath, json_encode($existingPackageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated package.json with Vue Admin Panel dependencies.');
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
            $this->info('Created package.json with Vue Admin Panel dependencies.');
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
        if (!File::exists($viewsPath)) {
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
        if (!File::exists($jsPath)) {
            File::makeDirectory($jsPath, 0755, true);
        }

        // Create css directory if it doesn't exist
        if (!File::exists($cssPath)) {
            File::makeDirectory($cssPath, 0755, true);
        }

        // Create app.js
        if (File::exists($appJsStub)) {
            if (File::exists($appJsPath)) {
                // Check if the existing file has proper Inertia setup
                $existingContent = File::get($appJsPath);
                $hasInertiaSetup = str_contains($existingContent, 'createInertiaApp') && 
                                   str_contains($existingContent, 'resolvePageComponent');
                
                if (!$hasInertiaSetup) {
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
        if (!File::exists($bootstrapJsPath)) {
            $bootstrapContent = <<<'JS'
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
JS;
            File::put($bootstrapJsPath, $bootstrapContent);
            $this->info('Created bootstrap.js');
        }

        // Create app.css if it doesn't exist
        if (!File::exists($appCssPath)) {
            $appCssContent = <<<'CSS'
@import "tailwindcss";
CSS;
            File::put($appCssPath, $appCssContent);
            $this->info('Created app.css');
        }
    }

    /**
     * Create login pages
     */
    protected function createLoginPages(): void
    {
        // Login pages are now in vendor at resources/js/vendor/inertia-resource/Pages/Auth/
        // Users can override by creating files in resources/js/Pages/Auth/
        // Vite will automatically prefer app files over vendor files
        $this->comment('   Login pages are available from vendor and can be imported from @/vendor/inertia-resource/Pages/Auth/');
        $this->comment('   To customize, create files in resources/js/Pages/Auth/ (they will override vendor versions)');
    }

    /**
     * Create admin routes
     */
    protected function createAdminRoutes(): void
    {
        // Create middleware files first
        $this->createAdminMiddleware();
        
        $routesPath = base_path('routes');
        $adminRoutesFile = "{$routesPath}/admin.php";
        $webRoutesFile = "{$routesPath}/web.php";

        // Check if web.php exists
        if (!File::exists($webRoutesFile)) {
            $this->warn('⚠️  Could not find routes/web.php file.');
            $this->comment('Please create routes/web.php and add the following manually:');
            $this->displayAdminRoutes();
            return;
        }

        // Create admin.php route file
        $adminRoutesContent = "<?php\n\n";
        $adminRoutesContent .= "use Inertia\\Inertia;\n";
        $adminRoutesContent .= "use InertiaResource\\Http\\Controllers\\UserColumnPreferenceController;\n\n";
        $adminRoutesContent .= "// Admin routes\n";
        $adminRoutesContent .= "Route::prefix('admin')->name('admin.')->group(function () {\n";
        $adminRoutesContent .= "    // Login routes (guest middleware - redirects to admin.dashboard if authenticated)\n";
        $adminRoutesContent .= "    Route::middleware([App\\Http\\Middleware\\RedirectIfAuthenticatedAdmin::class])->group(function () {\n";
        $adminRoutesContent .= "        Route::get('/login', function () {\n";
        $adminRoutesContent .= "            return Inertia::render('Auth/AdminLogin');\n";
        $adminRoutesContent .= "        })->name('login');\n";
        $adminRoutesContent .= "        \n";
        $adminRoutesContent .= "        Route::post('/login', function (\\Illuminate\\Http\\Request \$request) {\n";
        $adminRoutesContent .= "            \$credentials = \$request->validate([\n";
        $adminRoutesContent .= "                'email' => ['required', 'email'],\n";
        $adminRoutesContent .= "                'password' => ['required'],\n";
        $adminRoutesContent .= "            ]);\n";
        $adminRoutesContent .= "            \n";
        $adminRoutesContent .= "            if (\\Illuminate\\Support\\Facades\\Auth::attempt(\$credentials, \$request->boolean('remember'))) {\n";
        $adminRoutesContent .= "                \$request->session()->regenerate();\n";
        $adminRoutesContent .= "                return redirect()->intended(route('admin.dashboard'));\n";
        $adminRoutesContent .= "            }\n";
        $adminRoutesContent .= "            \n";
        $adminRoutesContent .= "            throw \\Illuminate\\Validation\\ValidationException::withMessages([\n";
        $adminRoutesContent .= "                'email' => 'The provided credentials do not match our records.',\n";
        $adminRoutesContent .= "            ]);\n";
        $adminRoutesContent .= "        })->name('login');\n";
        $adminRoutesContent .= "    });\n";
        $adminRoutesContent .= "    \n";
        $adminRoutesContent .= "    // Protected admin routes (auth middleware - redirects to admin.login if not authenticated)\n";
        $adminRoutesContent .= "    Route::middleware([App\\Http\\Middleware\\AuthenticateAdmin::class])->group(function () {\n";
        $adminRoutesContent .= "        Route::get('/', function () {\n";
        $adminRoutesContent .= "            return Inertia::render('Dashboard');\n";
        $adminRoutesContent .= "        })->name('dashboard');\n";
        $adminRoutesContent .= "        \n";
        $adminRoutesContent .= "        Route::post('/logout', function (\\Illuminate\\Http\\Request \$request) {\n";
        $adminRoutesContent .= "            \\Illuminate\\Support\\Facades\\Auth::logout();\n";
        $adminRoutesContent .= "            \$request->session()->invalidate();\n";
        $adminRoutesContent .= "            \$request->session()->regenerateToken();\n";
        $adminRoutesContent .= "            return redirect()->route('admin.login');\n";
        $adminRoutesContent .= "        })->name('logout');\n";
        $adminRoutesContent .= "    });\n";
        $adminRoutesContent .= "});\n\n";
        $adminRoutesContent .= "// API routes for column preferences\n";
        $adminRoutesContent .= "Route::prefix('api')->middleware([App\\Http\\Middleware\\AuthenticateAdmin::class, 'web'])->group(function () {\n";
        $adminRoutesContent .= "    Route::get('/user-column-preferences/{resourceSlug}', [UserColumnPreferenceController::class, 'show']);\n";
        $adminRoutesContent .= "    Route::post('/user-column-preferences/{resourceSlug}', [UserColumnPreferenceController::class, 'store']);\n";
        $adminRoutesContent .= "    Route::delete('/user-column-preferences/{resourceSlug}', [UserColumnPreferenceController::class, 'destroy']);\n";
        $adminRoutesContent .= "});\n";

        // Write admin.php file
        File::put($adminRoutesFile, $adminRoutesContent);
        $this->info('✅ Created routes/admin.php');

        // Update web.php to include admin.php
        $webRoutesContent = File::get($webRoutesFile);
        
        // Check if admin.php is already included
        if (strpos($webRoutesContent, "require __DIR__.'/admin.php';") === false && 
            strpos($webRoutesContent, "require __DIR__ . '/admin.php';") === false &&
            strpos($webRoutesContent, "require __DIR__ . \"/admin.php\";") === false) {
            
            // Add require statement at the end of web.php
            $webRoutesContent .= "\n\n// Admin routes\n";
            $webRoutesContent .= "require __DIR__.'/admin.php';\n";
            
            File::put($webRoutesFile, $webRoutesContent);
            $this->info('✅ Added admin routes include to routes/web.php');
        } else {
            $this->comment('⚠️  Admin routes already included in routes/web.php');
        }
    }

    /**
     * Display admin route definitions for manual addition
     */
    protected function displayAdminRoutes(): void
    {
        $this->newLine();
        $this->comment('Add these routes to your routes/web.php file:');
        $this->newLine();
        $this->line("use Inertia\\Inertia;");
        $this->newLine();
        $this->line("Route::prefix('admin')->name('admin.')->group(function () {");
        $this->line("    // Login routes (guest middleware)");
        $this->line("    Route::middleware(['guest'])->group(function () {");
        $this->line("        Route::get('/login', function () {");
        $this->line("            return Inertia::render('Auth/AdminLogin');");
        $this->line("        })->name('login');");
        $this->line("        ");
        $this->line("        Route::post('/login', function (\\Illuminate\\Http\\Request \$request) {");
        $this->line("            \$credentials = \$request->validate([");
        $this->line("                'email' => ['required', 'email'],");
        $this->line("                'password' => ['required'],");
        $this->line("            ]);");
        $this->line("            ");
        $this->line("            if (\\Illuminate\\Support\\Facades\\Auth::attempt(\$credentials, \$request->boolean('remember'))) {");
        $this->line("                \$request->session()->regenerate();");
        $this->line("                return redirect()->intended(route('admin.dashboard'));");
        $this->line("            }");
        $this->line("            ");
        $this->line("            throw \\Illuminate\\Validation\\ValidationException::withMessages([");
        $this->line("                'email' => 'The provided credentials do not match our records.',");
        $this->line("            ]);");
        $this->line("        })->name('login');");
        $this->line("    });");
        $this->line("    ");
        $this->line("    // Protected admin routes (auth middleware - uses default web guard)");
        $this->line("    Route::middleware(['auth'])->group(function () {");
        $this->line("        Route::get('/', function () {");
        $this->line("            return Inertia::render('Dashboard');");
        $this->line("        })->name('dashboard');");
        $this->line("        ");
        $this->line("        Route::post('/logout', function (\\Illuminate\\Http\\Request \$request) {");
        $this->line("            \\Illuminate\\Support\\Facades\\Auth::logout();");
        $this->line("            \$request->session()->invalidate();");
        $this->line("            \$request->session()->regenerateToken();");
        $this->line("            return redirect()->route('admin.login');");
        $this->line("        })->name('logout');");
        $this->line("    });");
        $this->line("});");
        $this->newLine();
    }

    /**
     * Create customer routes
     */
    protected function createCustomerRoutes(): void
    {
        $useCustomers = config('inertia-resource.use_customers', false);
        
        if (!$useCustomers) {
            return;
        }

        // Create middleware files first
        $this->createCustomerMiddleware();

        // Check if customer guard is configured, if not, configure it
        $this->ensureCustomerGuardConfigured();

        $routesPath = base_path('routes');
        $customerRoutesFile = "{$routesPath}/customer.php";
        $webRoutesFile = "{$routesPath}/web.php";

        // Check if web.php exists
        if (!File::exists($webRoutesFile)) {
            $this->warn('⚠️  Could not find routes/web.php file.');
            $this->comment('Please create routes/web.php and add the following manually:');
            $this->displayCustomerRoutes();
            return;
        }

        // Create customer.php route file
        $customerRoutesContent = "<?php\n\n";
        $customerRoutesContent .= "use Inertia\\Inertia;\n\n";
        $customerRoutesContent .= "// Customer routes (root level, uses customer guard)\n";
        $customerRoutesContent .= "// Customer login routes (guest middleware - redirects to customer.dashboard if authenticated)\n";
        $customerRoutesContent .= "Route::middleware([App\\Http\\Middleware\\RedirectIfAuthenticatedCustomer::class])->group(function () {\n";
        $customerRoutesContent .= "    Route::get('/login', function () {\n";
        $customerRoutesContent .= "        return Inertia::render('Auth/CustomerLogin');\n";
        $customerRoutesContent .= "    })->name('customer.login');\n";
        $customerRoutesContent .= "    \n";
        $customerRoutesContent .= "    Route::post('/login', function (\\Illuminate\\Http\\Request \$request) {\n";
        $customerRoutesContent .= "        \$credentials = \$request->validate([\n";
        $customerRoutesContent .= "            'email' => ['required', 'email'],\n";
        $customerRoutesContent .= "            'password' => ['required'],\n";
        $customerRoutesContent .= "        ]);\n";
        $customerRoutesContent .= "        \n";
        $customerRoutesContent .= "        if (\\Illuminate\\Support\\Facades\\Auth::guard('customer')->attempt(\$credentials, \$request->boolean('remember'))) {\n";
        $customerRoutesContent .= "            \$request->session()->regenerate();\n";
        $customerRoutesContent .= "            return redirect()->intended(route('customer.dashboard'));\n";
        $customerRoutesContent .= "        }\n";
        $customerRoutesContent .= "        \n";
        $customerRoutesContent .= "        throw \\Illuminate\\Validation\\ValidationException::withMessages([\n";
        $customerRoutesContent .= "            'email' => 'The provided credentials do not match our records.',\n";
        $customerRoutesContent .= "        ]);\n";
        $customerRoutesContent .= "    })->name('customer.login.post');\n";
        $customerRoutesContent .= "});\n\n";
        $customerRoutesContent .= "// Protected customer routes (auth:customer guard - redirects to customer.login if not authenticated)\n";
        $customerRoutesContent .= "Route::middleware([App\\Http\\Middleware\\AuthenticateCustomer::class])->group(function () {\n";
        $customerRoutesContent .= "    Route::get('/', function () {\n";
        $customerRoutesContent .= "        return Inertia::render('Dashboard');\n";
        $customerRoutesContent .= "    })->name('customer.dashboard');\n";
        $customerRoutesContent .= "    \n";
        $customerRoutesContent .= "    Route::post('/logout', function (\\Illuminate\\Http\\Request \$request) {\n";
        $customerRoutesContent .= "        \\Illuminate\\Support\\Facades\\Auth::guard('customer')->logout();\n";
        $customerRoutesContent .= "        \$request->session()->invalidate();\n";
        $customerRoutesContent .= "        \$request->session()->regenerateToken();\n";
        $customerRoutesContent .= "        return redirect()->route('customer.login');\n";
        $customerRoutesContent .= "    })->name('customer.logout');\n";
        $customerRoutesContent .= "});\n";

        // Write customer.php file
        File::put($customerRoutesFile, $customerRoutesContent);
        $this->info('✅ Created routes/customer.php');

        // Update web.php to include customer.php
        $webRoutesContent = File::get($webRoutesFile);
        
        // Check if customer.php is already included
        if (strpos($webRoutesContent, "require __DIR__.'/customer.php';") === false && 
            strpos($webRoutesContent, "require __DIR__ . '/customer.php';") === false &&
            strpos($webRoutesContent, "require __DIR__ . \"/customer.php\";") === false) {
            
            // Add require statement at the end of web.php
            $webRoutesContent .= "\n\n// Customer routes\n";
            $webRoutesContent .= "require __DIR__.'/customer.php';\n";
            
            File::put($webRoutesFile, $webRoutesContent);
            $this->info('✅ Added customer routes include to routes/web.php');
        } else {
            $this->comment('⚠️  Customer routes already included in routes/web.php');
        }
    }

    /**
     * Display customer route definitions for manual addition
     */
    protected function displayCustomerRoutes(): void
    {
        $this->newLine();
        $this->comment('Add these routes to your routes/web.php file:');
        $this->newLine();
        $this->line("use Inertia\\Inertia;");
        $this->newLine();
        $this->line("// Customer routes (root level, uses customer guard)");
        $this->line("// Customer login routes (guest middleware - redirects to /login if already authenticated)");
        $this->line("Route::middleware(['guest:customer'])->group(function () {");
        $this->line("    Route::get('/login', function () {");
        $this->line("        return Inertia::render('Auth/CustomerLogin');");
        $this->line("    })->name('customer.login');");
        $this->line("    ");
        $this->line("    Route::post('/login', function (\\Illuminate\\Http\\Request \$request) {");
        $this->line("        \$credentials = \$request->validate([");
        $this->line("            'email' => ['required', 'email'],");
        $this->line("            'password' => ['required'],");
        $this->line("        ]);");
        $this->line("        ");
        $this->line("        if (\\Illuminate\\Support\\Facades\\Auth::guard('customer')->attempt(\$credentials, \$request->boolean('remember'))) {");
        $this->line("            \$request->session()->regenerate();");
        $this->line("            return redirect()->intended('/');");
        $this->line("        }");
        $this->line("        ");
        $this->line("        throw \\Illuminate\\Validation\\ValidationException::withMessages([");
        $this->line("            'email' => 'The provided credentials do not match our records.',");
        $this->line("        ]);");
        $this->line("    })->name('customer.login.post');");
        $this->line("});");
        $this->newLine();
        $this->line("// Protected customer routes (auth:customer guard - redirects to /login if not authenticated)");
        $this->line("Route::middleware(['auth:customer'])->group(function () {");
        $this->line("    Route::get('/', function () {");
        $this->line("        return Inertia::render('Dashboard');");
        $this->line("    })->name('customer.dashboard');");
        $this->line("    ");
        $this->line("    Route::post('/logout', function (\\Illuminate\\Http\\Request \$request) {");
        $this->line("        \\Illuminate\\Support\\Facades\\Auth::guard('customer')->logout();");
        $this->line("        \$request->session()->invalidate();");
        $this->line("        \$request->session()->regenerateToken();");
        $this->line("        return redirect()->route('customer.login');");
        $this->line("    })->name('customer.logout');");
        $this->line("});");
        $this->newLine();
    }

    /**
     * Create admin authentication middleware
     */
    protected function createAdminMiddleware(): void
    {
        $middlewarePath = app_path('Http/Middleware');
        
        if (!File::exists($middlewarePath)) {
            File::makeDirectory($middlewarePath, 0755, true);
        }
        
        // Create HandleInertiaRequests middleware if it doesn't exist
        $this->createHandleInertiaRequestsMiddleware();
        
        // Create AuthenticateAdmin middleware
        $authenticateAdminPath = "{$middlewarePath}/AuthenticateAdmin.php";
        if (!File::exists($authenticateAdminPath)) {
            $stub = File::get(__DIR__.'/../../stubs/Middleware/AuthenticateAdmin.stub');
            File::put($authenticateAdminPath, $stub);
            $this->info('✅ Created AuthenticateAdmin middleware');
        }
        
        // Create RedirectIfAuthenticatedAdmin middleware
        $redirectIfAuthAdminPath = "{$middlewarePath}/RedirectIfAuthenticatedAdmin.php";
        if (!File::exists($redirectIfAuthAdminPath)) {
            $stub = File::get(__DIR__.'/../../stubs/Middleware/RedirectIfAuthenticatedAdmin.stub');
            File::put($redirectIfAuthAdminPath, $stub);
            $this->info('✅ Created RedirectIfAuthenticatedAdmin middleware');
        }
    }

    /**
     * Create HandleInertiaRequests middleware if it doesn't exist
     */
    protected function createHandleInertiaRequestsMiddleware(): void
    {
        $middlewarePath = app_path('Http/Middleware');
        
        if (!File::exists($middlewarePath)) {
            File::makeDirectory($middlewarePath, 0755, true);
        }
        
        $handleInertiaPath = "{$middlewarePath}/HandleInertiaRequests.php";
        $wasCreated = false;
        
        if (!File::exists($handleInertiaPath)) {
            $stubPath = __DIR__.'/../../stubs/Middleware/HandleInertiaRequests.stub';
            if (File::exists($stubPath)) {
                $stub = File::get($stubPath);
                File::put($handleInertiaPath, $stub);
                $this->info('✅ Created HandleInertiaRequests middleware');
                $wasCreated = true;
            } else {
                $this->warn('⚠️  HandleInertiaRequests.stub not found. Skipping middleware creation.');
            }
        } else {
            $this->comment('ℹ️  HandleInertiaRequests middleware already exists. Skipping creation.');
        }
        
        // Register middleware in bootstrap/app.php if it was created or if not registered
        if ($wasCreated || File::exists($handleInertiaPath)) {
            $this->registerHandleInertiaRequestsMiddleware();
        }
    }

    /**
     * Register HandleInertiaRequests middleware in bootstrap/app.php
     */
    protected function registerHandleInertiaRequestsMiddleware(): void
    {
        $bootstrapAppPath = base_path('bootstrap/app.php');
        
        if (!File::exists($bootstrapAppPath)) {
            $this->warn('⚠️  bootstrap/app.php not found. Please register HandleInertiaRequests middleware manually.');
            return;
        }
        
        $content = File::get($bootstrapAppPath);
        
        // Check if middleware is already registered
        if (strpos($content, 'HandleInertiaRequests') !== false) {
            $this->comment('ℹ️  HandleInertiaRequests middleware already registered in bootstrap/app.php');
            return;
        }
        
        // Check if withMiddleware block exists and is empty or has content
        $middlewarePattern = '/->withMiddleware\s*\(\s*function\s*\(Middleware\s+\$middleware\):\s*void\s*\{([^}]*)\}\s*\)/s';
        
        if (preg_match($middlewarePattern, $content, $matches)) {
            $middlewareBlock = $matches[1];
            
            // Check if it's empty (just whitespace/comments)
            if (trim($middlewareBlock) === '' || trim($middlewareBlock) === '//') {
                // Empty block - add middleware registration
                $replacement = "->withMiddleware(function (Middleware \$middleware): void {\n        \$middleware->web(append: [\n            \\App\\Http\\Middleware\\HandleInertiaRequests::class,\n        ]);\n    })";
                $content = preg_replace($middlewarePattern, $replacement, $content);
            } else {
                // Has content - append to existing
                if (strpos($middlewareBlock, '->web(') !== false) {
                    // Append to existing web() call
                    $content = preg_replace(
                        '/(\$middleware->web\([^)]*)\)/',
                        '$1, \\App\\Http\\Middleware\\HandleInertiaRequests::class)',
                        $content
                    );
                } else {
                    // Add new web() call
                    $replacement = "->withMiddleware(function (Middleware \$middleware): void {\n        \$middleware->web(append: [\n            \\App\\Http\\Middleware\\HandleInertiaRequests::class,\n        ]);\n{$middlewareBlock}\n    })";
                    $content = preg_replace($middlewarePattern, $replacement, $content);
                }
            }
            
            File::put($bootstrapAppPath, $content);
            $this->info('✅ Registered HandleInertiaRequests middleware in bootstrap/app.php');
        } else {
            $this->warn('⚠️  Could not find withMiddleware block in bootstrap/app.php. Please register HandleInertiaRequests middleware manually:');
            $this->line('   $middleware->web(append: [\\App\\Http\\Middleware\\HandleInertiaRequests::class]);');
        }
    }
    
    /**
     * Create customer authentication middleware
     */
    protected function createCustomerMiddleware(): void
    {
        $middlewarePath = app_path('Http/Middleware');
        
        if (!File::exists($middlewarePath)) {
            File::makeDirectory($middlewarePath, 0755, true);
        }
        
        // Create AuthenticateCustomer middleware
        $authenticateCustomerPath = "{$middlewarePath}/AuthenticateCustomer.php";
        if (!File::exists($authenticateCustomerPath)) {
            $stub = File::get(__DIR__.'/../../stubs/Middleware/AuthenticateCustomer.stub');
            File::put($authenticateCustomerPath, $stub);
            $this->info('✅ Created AuthenticateCustomer middleware');
        }
        
        // Create RedirectIfAuthenticatedCustomer middleware
        $redirectIfAuthCustomerPath = "{$middlewarePath}/RedirectIfAuthenticatedCustomer.php";
        if (!File::exists($redirectIfAuthCustomerPath)) {
            $stub = File::get(__DIR__.'/../../stubs/Middleware/RedirectIfAuthenticatedCustomer.stub');
            File::put($redirectIfAuthCustomerPath, $stub);
            $this->info('✅ Created RedirectIfAuthenticatedCustomer middleware');
        }
    }

    /**
     * Clean up incorrectly placed routes from web.php
     */
    protected function cleanupWebRoutes(): void
    {
        $webRoutesFile = base_path('routes/web.php');
        
        if (!File::exists($webRoutesFile)) {
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
            
            // Check if this line is a require statement for admin.php or customer.php
            if (preg_match("/require\s+__DIR__\s*\.\s*['\"]\/admin\.php['\"]/", $line) ||
                preg_match("/require\s+__DIR__\s*\.\s*['\"]\/customer\.php['\"]/", $line)) {
                $cleanedLines[] = $line;
                continue;
            }

            // Check if we're starting an admin route group (not in a require statement)
            if (!$inAdminGroup && preg_match("/Route::prefix\(['\"]admin['\"]\)/", $line)) {
                $inAdminGroup = true;
                $skipUntilBraceCount = $braceCount;
                $braceCount += substr_count($line, '(') - substr_count($line, ')');
                $cleaned = true;
                continue; // Skip this line
            }

            // Check if we're starting a customer route group (not in a require statement)
            if (!$inAdminGroup && preg_match("/Route::middleware\(\[.*AuthenticateCustomer/", $line)) {
                $inAdminGroup = true;
                $skipUntilBraceCount = $braceCount;
                $braceCount += substr_count($line, '(') - substr_count($line, ')');
                $cleaned = true;
                continue; // Skip this line
            }

            // If we're skipping an admin/customer route group, track braces
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
            $this->info('✅ Removed incorrectly placed admin/customer routes from web.php');
            $this->comment('   Routes should be in admin.php or customer.php, not directly in web.php');
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
        
        if (!File::exists($viteConfigPath)) {
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
            
            if (!$needsFix) {
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
            !preg_match("/import\s+laravel\s*,\s*\{[^}]*refreshPaths[^}]*\}\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent)) {
            
            if (!$needsFix) {
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
            !preg_match("/import\s+laravel\s*,\s*\{[^}]*refreshPaths[^}]*\}\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent)) {
            
            if (!$needsFix) {
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
        if (str_contains($fixedContent, "refresh: true") && 
            str_contains($fixedContent, "from 'laravel-vite-plugin'")) {
            
            if (!$needsFix) {
                $this->comment('Updating refresh configuration...');
            }
            
            $fixedContent = preg_replace(
                "/refresh:\s*true/",
                "refresh: refreshPaths",
                $fixedContent
            );
            
            // Ensure refreshPaths is imported
            if (!preg_match("/import\s+laravel\s*,\s*\{[^}]*refreshPaths[^}]*\}\s+from\s+['\"]laravel-vite-plugin['\"]/", $fixedContent)) {
                $fixedContent = preg_replace(
                    "/import\s+laravel\s+from\s+['\"]laravel-vite-plugin['\"]/",
                    "import laravel, { refreshPaths } from 'laravel-vite-plugin'",
                    $fixedContent
                );
            }
            
            $needsFix = true;
        }
        
        // Check for missing Vue plugin
        $hasVueImport = str_contains($fixedContent, "@vitejs/plugin-vue") || str_contains($fixedContent, '@vitejs/plugin-vue');
        $hasVuePlugin = str_contains($fixedContent, 'vue(') || str_contains($fixedContent, 'vue({');
        
        if (!$hasVueImport || !$hasVuePlugin) {
            if (!$needsFix) {
                $this->warn('⚠️  Missing Vue plugin in vite.config.js');
                $this->comment('   Adding Vue plugin...');
            }
            
            // Add Vue import if missing
            if (!$hasVueImport) {
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
            if (!$hasVuePlugin) {
                // Find the laravel plugin and add vue after it
                if (preg_match("/(laravel\(\{[^}]*\}\)),?\s*/", $fixedContent, $matches)) {
                    $vuePlugin = "\n        vue({\n            template: {\n                transformAssetUrls: {\n                    base: null,\n                    includeAbsolute: false,\n                },\n            },\n        }),";
                    $fixedContent = preg_replace(
                        "/(laravel\(\{[^}]*\}\)),?\s*/",
                        "$1," . $vuePlugin,
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
        if (!str_contains($fixedContent, "resolve:") || !str_contains($fixedContent, "'@':") || !str_contains($fixedContent, "'@': '/resources/js'")) {
            if (!$needsFix) {
                $this->warn('⚠️  Missing resolve alias in vite.config.js');
                $this->comment('   Adding resolve alias...');
            }
            
            // Check if resolve already exists
            if (str_contains($fixedContent, 'resolve:')) {
                // Add alias to existing resolve
                if (!str_contains($fixedContent, "'@':") && !str_contains($fixedContent, '"@":')) {
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
                   str_contains($viteConfigContent, "resolve:")) {
            $this->comment('✓ vite.config.js already has correct configuration.');
        }
    }

    /**
     * Create admin layouts and dashboard
     */
    protected function createAdminLayouts(): void
    {
        // Layouts, dashboard, and components are now in vendor at resources/js/vendor/inertia-resource/
        // Users can override by creating files in resources/js/ (Layouts/, Pages/, Components/)
        // Vite will automatically prefer app files over vendor files
        $this->comment('   Layouts, dashboard, and components are available from vendor');
        $this->comment('   To customize, create files in resources/js/ (they will override vendor versions)');
        $this->comment('   Example: Create resources/js/Layouts/AdminLayout.vue to override the vendor version');
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
     * Create menu system (models, migrations, routes, and Vue files)
     */
    protected function createMenuSystem(): void
    {
        // Step 1: Check if migrations exist before creating them
        $menuGroupsMigrationExists = $this->checkMigrationExists('create_menu_groups_table');
        $menuItemsMigrationExists = $this->checkMigrationExists('create_menu_items_table');
        $userColumnPrefsMigrationExists = $this->checkMigrationExists('create_user_column_preferences_table');

        $needsMigrations = !$menuGroupsMigrationExists || !$menuItemsMigrationExists || !$userColumnPrefsMigrationExists;

        if ($needsMigrations) {
            // Publish migrations if they don't exist
            $this->info('📦 Publishing menu migrations...');
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-migrations',
                '--force' => false,
            ]);
            $this->info('✅ Menu migrations published.');
        } else {
            $this->comment('ℹ️  Menu migrations already exist. Skipping migration creation.');
        }

        // Step 2: Create models if they don't exist
        $menuGroupPath = app_path('Models/MenuGroup.php');
        $menuItemPath = app_path('Models/MenuItem.php');

        $needsModels = !File::exists($menuGroupPath) || !File::exists($menuItemPath);

        if ($needsModels) {
            $this->info('📄 Publishing menu models...');
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-menu-models',
                '--force' => false,
            ]);
            $this->info('✅ Menu models published.');
        } else {
            $this->comment('ℹ️  Menu models already exist. Skipping model creation.');
        }

        // Step 3: Create Inertia Resources for MenuGroup and MenuItem
        // Wait a moment for autoloader to catch up if models were just created
        if ($needsModels) {
            // Clear and rebuild autoloader cache
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            // Give autoloader a moment
            usleep(500000); // 0.5 seconds
        }

        $this->newLine();
        $this->info('📦 Creating MenuGroup Resource...');
        
        if (class_exists('App\\Models\\MenuGroup')) {
            $this->call('make:inertia-resource', [
                'model' => 'App\\Models\\MenuGroup',
                '--all' => true,
            ]);
            $this->info('✅ MenuGroup Resource created.');
        } else {
            $this->warn('⚠️  MenuGroup model not found. Please run migrations first: php artisan migrate');
            $this->comment('   Then run: php artisan make:inertia-resource "App\\Models\\MenuGroup" --all');
        }

        $this->newLine();
        $this->info('📦 Creating MenuItem Resource...');
        
        if (class_exists('App\\Models\\MenuItem')) {
            $this->call('make:inertia-resource', [
                'model' => 'App\\Models\\MenuItem',
                '--all' => true,
            ]);
            $this->info('✅ MenuItem Resource created.');
        } else {
            $this->warn('⚠️  MenuItem model not found. Please run migrations first: php artisan migrate');
            $this->comment('   Then run: php artisan make:inertia-resource "App\\Models\\MenuItem" --all');
        }

        // Verify routes were added to routes/admin.php
        $this->newLine();
        $this->info('🔍 Verifying routes...');
        $adminRoutesFile = base_path('routes/admin.php');
        $menuGroupRoutesAdded = false;
        $menuItemRoutesAddedToFile = false;
        
        if (File::exists($adminRoutesFile)) {
            $routesContent = File::get($adminRoutesFile);
            
            // Check for menu-groups routes
            if (strpos($routesContent, "Route::prefix('menu-groups')") !== false || 
                strpos($routesContent, 'Route::prefix("menu-groups")') !== false) {
                $menuGroupRoutesAdded = true;
                $this->info('✅ MenuGroup routes found in routes/admin.php');
            } else {
                $this->warn('⚠️  MenuGroup routes not found in routes/admin.php');
            }
            
            // Check for menu-items routes
            if (strpos($routesContent, "Route::prefix('menu-items')") !== false || 
                strpos($routesContent, 'Route::prefix("menu-items")') !== false) {
                $menuItemRoutesAddedToFile = true;
                $this->info('✅ MenuItem routes found in routes/admin.php');
            } else {
                $this->warn('⚠️  MenuItem routes not found in routes/admin.php');
            }
        } else {
            $this->warn('⚠️  routes/admin.php not found. Routes may need to be added manually.');
        }

        $this->newLine();
        $this->info('✅ Menu system setup complete!');
        
        // Create menu items for User and Customer resources if they exist
        $this->createResourceMenuItems();
        
        $this->newLine();
        $this->comment('📋 Next steps:');
        if ($needsMigrations) {
            $this->comment('   1. Run migrations: php artisan migrate');
        }
        $this->comment('   2. Seed your menu data in the database');
        $this->comment('   3. Use MenuBuilder::build() to share menu data with Inertia');
        if ($menuGroupRoutesAdded && $menuItemRoutesAddedToFile) {
            $this->comment('   4. Access menu management at: /admin/menu-groups and /admin/menu-items');
        } else {
            $this->comment('   4. Access menu management at: /admin/menu-groups and /admin/menu-items');
            if (!$menuGroupRoutesAdded || !$menuItemRoutesAddedToFile) {
                $this->warn('   ⚠️  Note: Some routes may need to be added manually to routes/admin.php');
            }
        }
    }

    /**
     * Create menu items for User and Customer resources if they exist
     */
    protected function createResourceMenuItems(): void
    {
        // Check if menu tables exist
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('menu_groups') || 
                !\Illuminate\Support\Facades\Schema::hasTable('menu_items')) {
                $this->comment('ℹ️  Menu tables not found. Skipping resource menu item creation.');
                return;
            }
        } catch (\Exception $e) {
            $this->comment('ℹ️  Could not check menu tables. Skipping resource menu item creation.');
            return;
        }

        // Check if MenuGroup and MenuItem models exist
        if (!class_exists('App\\Models\\MenuGroup') || !class_exists('App\\Models\\MenuItem')) {
            $this->comment('ℹ️  Menu models not found. Skipping resource menu item creation.');
            return;
        }

        $appNamespace = $this->getAppNamespace();
        $resourceNamespace = $appNamespace . 'Support\\Inertia\\Resources';

        // Create or get "Administration" menu group
        $adminGroup = \App\Models\MenuGroup::firstOrCreate(
            ['key' => 'administration'],
            [
                'label' => 'Administration',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $sortOrder = 1;

        // Check for UserResource
        $userResourceClass = $resourceNamespace . '\\UserResource';
        if (class_exists($userResourceClass)) {
            try {
                $userSlug = $userResourceClass::getSlug();
                $userTitle = $userResourceClass::getTitle() ?? 'Users';
                
                if ($userSlug) {
                    \App\Models\MenuItem::firstOrCreate(
                        [
                            'menu_group_id' => $adminGroup->id,
                            'key' => $userSlug,
                        ],
                        [
                            'label' => $userTitle,
                            'route' => "admin.{$userSlug}.index",
                            'sort_order' => $sortOrder++,
                            'is_active' => true,
                            'parent_id' => null,
                        ]
                    );
                    $this->info("✅ Created menu item for {$userTitle} in Administration group.");
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Could not create menu item for UserResource: " . $e->getMessage());
            }
        }

        // Check for CustomerResource
        $customerResourceClass = $resourceNamespace . '\\CustomerResource';
        if (class_exists($customerResourceClass)) {
            try {
                $customerSlug = $customerResourceClass::getSlug();
                $customerTitle = $customerResourceClass::getTitle() ?? 'Customers';
                
                if ($customerSlug) {
                    \App\Models\MenuItem::firstOrCreate(
                        [
                            'menu_group_id' => $adminGroup->id,
                            'key' => $customerSlug,
                        ],
                        [
                            'label' => $customerTitle,
                            'route' => "admin.{$customerSlug}.index",
                            'sort_order' => $sortOrder++,
                            'is_active' => true,
                            'parent_id' => null,
                        ]
                    );
                    $this->info("✅ Created menu item for {$customerTitle} in Administration group.");
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Could not create menu item for CustomerResource: " . $e->getMessage());
            }
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

    /**
     * Create and run ResourceMenuSeeder (creates menu items for UserResource and CustomerResource)
     */
    protected function createAndRunResourceMenuSeeder(): void
    {
        // Ensure menu migrations are run first
        $this->ensureMenuMigrationsRun();
        
        $seedersPath = database_path('seeders');
        $seederFile = "{$seedersPath}/ResourceMenuSeeder.php";
        $seederStub = __DIR__.'/../../database/seeders/ResourceMenuSeeder.php.stub';
        
        // Check if seeder already exists
        if (File::exists($seederFile)) {
            $this->comment('ℹ️  ResourceMenuSeeder already exists. Skipping creation.');
        } else {
            if (File::exists($seederStub)) {
                // Create seeders directory if it doesn't exist
                if (!File::exists($seedersPath)) {
                    File::makeDirectory($seedersPath, 0755, true);
                }
                
                File::copy($seederStub, $seederFile);
                $this->info('✅ Created ResourceMenuSeeder.php');
            } else {
                $this->warn('⚠️  ResourceMenuSeeder.php.stub not found.');
                return;
            }
        }
        
        // Run the seeder
        $this->runSeeder('ResourceMenuSeeder');
    }

    /**
     * Ensure menu migrations are run before seeders
     */
    protected function ensureMenuMigrationsRun(): void
    {
        $migrationsPath = database_path('migrations');
        $menuGroupsMigration = glob($migrationsPath . '/*_create_menu_groups_table.php');
        $menuItemsMigration = glob($migrationsPath . '/*_create_menu_items_table.php');
        
        // Check if migrations exist
        if (empty($menuGroupsMigration) || empty($menuItemsMigration)) {
            $this->warn('⚠️  Menu migrations not found. Publishing migrations...');
            $this->call('vendor:publish', [
                '--tag' => 'inertia-resource-migrations',
                '--force' => false,
            ]);
        }
        
        // Check if migrations have been run by checking if tables exist
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('menu_groups') && 
                \Illuminate\Support\Facades\Schema::hasTable('menu_items')) {
                // Tables exist, migrations have been run
                return;
            }
        } catch (\Exception $e) {
            // Database connection might not be available, that's okay
        }
        
        // Ask user if they want to run migrations
        if ($this->confirm('Menu migrations need to be run before seeders. Run migrations now?', true)) {
            $this->info('🔄 Running migrations...');
            $this->call('migrate', ['--force' => true]);
            $this->info('✅ Migrations completed.');
        } else {
            $this->warn('⚠️  Skipping migration run. Please run migrations manually: php artisan migrate');
        }
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
            $this->warn("⚠️  Could not run {$seederClass}: " . $e->getMessage());
            $this->comment("   Please run manually: php artisan db:seed --class={$seederClass}");
        }
    }

    /**
     * Check if a migration exists
     */
    protected function checkMigrationExists(string $migrationName): bool
    {
        $migrationsPath = database_path('migrations');
        $pattern = $migrationsPath . '/*_' . $migrationName . '.php';
        $migrations = glob($pattern);
        return !empty($migrations);
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
        
        if (!File::exists($modelDir)) {
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
     * Create Customer migration
     */
    protected function createCustomerMigration(string $modelName): void
    {
        $tableName = strtolower($modelName) . 's';
        $migrationName = 'create_' . $tableName . '_table';
        
        if ($this->checkMigrationExists($migrationName)) {
            $this->comment("ℹ️  Customer migration already exists.");
            if ($this->useEnhancedFields) {
                $this->updateCustomerMigration($modelName);
            }
            return;
        }
        
        $this->call('make:migration', [
            'name' => $migrationName,
        ]);
        
        $this->info("✅ Created Customer migration: {$migrationName}");
        
        if ($this->useEnhancedFields) {
            $this->updateCustomerMigration($modelName);
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
        if (strpos($content, "first_name") !== false) {
            $this->comment('   ℹ️  Enhanced fields already exist in User migration.');
            return;
        }
        
        // Find the Schema::create block and add fields
        // Look for the id() line and add fields after it, before timestamps
        if (preg_match('/(\$table->id\(\);)/', $content, $matches)) {
            $enhancedFields = 
                "            \$table->id();\n" .
                "            \$table->string('first_name');\n" .
                "            \$table->string('last_name');\n" .
                "            \$table->string('email')->unique();\n" .
                "            \$table->string('mobile_number')->nullable();\n" .
                "            \$table->timestamp('email_verified_at')->nullable();\n" .
                "            \$table->string('password');\n" .
                "            \$table->rememberToken();\n";
            
            // Replace id() with enhanced fields, then look for timestamps
            $content = preg_replace('/\$table->id\(\);/', $enhancedFields, $content, 1);
            
            // Remove duplicate timestamps if they exist
            $content = preg_replace('/\$table->timestamps\(\);\s*\$table->timestamps\(\);/', '$table->timestamps();', $content);
        } else {
            // Fallback: try to find where to insert before timestamps
            $pattern = '/(function\s*\(Blueprint\s+\$table\)\s*\{[\s\S]*?)(\$table->timestamps\(\);)/';
            $replacement = '$1' . 
                "            \$table->string('first_name');\n" .
                "            \$table->string('last_name');\n" .
                "            \$table->string('email')->unique();\n" .
                "            \$table->string('mobile_number')->nullable();\n" .
                "            \$table->timestamp('email_verified_at')->nullable();\n" .
                "            \$table->string('password');\n" .
                "            \$table->rememberToken();\n" .
                '            $2';
            
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        File::put($migrationFile, $content);
        $this->info('   ✅ Added enhanced fields to User migration.');
    }

    /**
     * Update Customer migration with enhanced fields
     */
    protected function updateCustomerMigration(string $modelName): void
    {
        $tableName = strtolower($modelName) . 's';
        $migrationFiles = glob(database_path("migrations/*_create_{$tableName}_table.php"));
        if (empty($migrationFiles)) {
            return;
        }
        
        $migrationFile = $migrationFiles[0];
        $content = File::get($migrationFile);
        
        // Check if fields already exist
        if (strpos($content, "first_name") !== false) {
            $this->comment("   ℹ️  Enhanced fields already exist in Customer migration.");
            return;
        }
        
        // Find the Schema::create block and add fields
        // Look for the id() line and add fields after it, before timestamps
        if (preg_match('/(\$table->id\(\);)/', $content, $matches)) {
            $enhancedFields = 
                "            \$table->id();\n" .
                "            \$table->string('first_name');\n" .
                "            \$table->string('last_name');\n" .
                "            \$table->string('email')->unique();\n" .
                "            \$table->string('mobile_number')->nullable();\n";
            
            // Replace id() with enhanced fields, then look for timestamps
            $content = preg_replace('/\$table->id\(\);/', $enhancedFields, $content, 1);
            
            // Remove duplicate timestamps if they exist
            $content = preg_replace('/\$table->timestamps\(\);\s*\$table->timestamps\(\);/', '$table->timestamps();', $content);
        } else {
            // Fallback: try to find where to insert before timestamps
            $pattern = '/(function\s*\(Blueprint\s+\$table\)\s*\{[\s\S]*?)(\$table->timestamps\(\);)/';
            $replacement = '$1' . 
                "            \$table->string('first_name');\n" .
                "            \$table->string('last_name');\n" .
                "            \$table->string('email')->unique();\n" .
                "            \$table->string('mobile_number')->nullable();\n" .
                '            $2';
            
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        File::put($migrationFile, $content);
        $this->info("   ✅ Added enhanced fields to Customer migration.");
    }

    /**
     * Update User model with enhanced fields in fillable
     */
    protected function updateUserModel(): void
    {
        $modelPath = app_path('Models/User.php');
        if (!File::exists($modelPath)) {
            // Try alternative location
            $modelPath = app_path('User.php');
            if (!File::exists($modelPath)) {
                return;
            }
        }
        
        $content = File::get($modelPath);
        
        // Check if fields already exist
        if (strpos($content, "first_name") !== false) {
            $this->comment('   ℹ️  Enhanced fields already exist in User model.');
            return;
        }
        
        // Update fillable array
        $pattern = "/(protected\s+\$fillable\s*=\s*\[)([^\]]*)(\];)/s";
        $replacement = '$1' . 
            "\n        'first_name',\n" .
            "        'last_name',\n" .
            "        'email',\n" .
            "        'mobile_number',\n" .
            "        'password',\n" .
            "        'email_verified_at',\n" .
            '    $3';
        
        $content = preg_replace($pattern, $replacement, $content);
        
        File::put($modelPath, $content);
        $this->info('   ✅ Added enhanced fields to User model.');
    }

    /**
     * Update Customer model with enhanced fields in fillable
     */
    protected function updateCustomerModel(string $customerModel): void
    {
        if (!$customerModel) {
            return;
        }
        
        // Extract model name and namespace
        $modelParts = explode('\\', $customerModel);
        $modelName = end($modelParts);
        $namespace = implode('\\', array_slice($modelParts, 0, -1));
        
        // Determine model path
        if ($namespace === 'App\\Models' || $namespace === 'App') {
            $modelPath = app_path('Models/' . $modelName . '.php');
        } else {
            $relativePath = str_replace('App\\', '', $namespace);
            $relativePath = str_replace('\\', '/', $relativePath);
            $modelPath = app_path($relativePath . '/' . $modelName . '.php');
        }
        
        if (!File::exists($modelPath)) {
            return;
        }
        
        $content = File::get($modelPath);
        
        // Check if fields already exist
        if (strpos($content, "first_name") !== false) {
            $this->comment("   ℹ️  Enhanced fields already exist in Customer model.");
            return;
        }
        
        // Update fillable array
        $pattern = "/(protected\s+\$fillable\s*=\s*\[)([^\]]*)(\];)/s";
        $replacement = '$1' . 
            "\n        'first_name',\n" .
            "        'last_name',\n" .
            "        'email',\n" .
            "        'mobile_number',\n" .
            '    $3';
        
        $content = preg_replace($pattern, $replacement, $content);
        
        File::put($modelPath, $content);
        $this->info("   ✅ Added enhanced fields to Customer model.");
    }

    /**
     * Update User Resource with enhanced columns and form fields
     */
    protected function updateUserResource(): void
    {
        $resourcePath = app_path('Support/Inertia/Resources/UserResource.php');
        if (!File::exists($resourcePath)) {
            return;
        }
        
        $content = File::get($resourcePath);
        
        // Check if enhanced fields already exist
        if (strpos($content, "first_name") !== false) {
            $this->comment('   ℹ️  Enhanced fields already exist in User Resource.');
            return;
        }
        
        // Add imports if needed
        if (strpos($content, "use InertiaResource\\FormFields\\TextField;") === false) {
            $content = str_replace(
                "use InertiaResource\\Columns\\TextColumn;",
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
            $columnsReplacement = '$1' . 
                "\n                TextColumn::make('first_name', 'First Name'),\n" .
                "                TextColumn::make('last_name', 'Last Name'),\n" .
                "                TextColumn::make('mobile_number', 'Mobile Number'),";
            $content = preg_replace($columnsPattern, $columnsReplacement, $content);
        }
        
        // Update form fields in form() method
        $formPattern = "/(\/\/ Add your form fields here[\s\S]*?)(\]\s*;)/";
        if (preg_match($formPattern, $content)) {
            $formReplacement = '$1' . 
                "\n            TextField::make('first_name', 'First Name')->required(),\n" .
                "            TextField::make('last_name', 'Last Name')->required(),\n" .
                "            TextField::make('email', 'Email')->type('email')->required(),\n" .
                "            TextField::make('mobile_number', 'Mobile Number'),\n" .
                "            TextField::make('password', 'Password')->type('password'),\n" .
                '        $2';
            $content = preg_replace($formPattern, $formReplacement, $content);
        } else {
            // If no placeholder, add fields before the closing bracket
            $formPattern2 = "/(return\s+\[)([\s\S]*?)(\]\s*;\s*}\s*public static function)/";
            $formReplacement2 = '$1' . 
                "\n            TextField::make('first_name', 'First Name')->required(),\n" .
                "            TextField::make('last_name', 'Last Name')->required(),\n" .
                "            TextField::make('email', 'Email')->type('email')->required(),\n" .
                "            TextField::make('mobile_number', 'Mobile Number'),\n" .
                "            TextField::make('password', 'Password')->type('password'),\n" .
                '        $3';
            $content = preg_replace($formPattern2, $formReplacement2, $content);
        }
        
        File::put($resourcePath, $content);
        $this->info('   ✅ Added enhanced fields to User Resource.');
    }

    /**
     * Update Customer Resource with enhanced columns and form fields
     */
    protected function updateCustomerResource(string $customerModel): void
    {
        if (!$customerModel) {
            return;
        }
        
        // Extract model name
        $modelParts = explode('\\', $customerModel);
        $modelName = end($modelParts);
        $resourceName = $modelName . 'Resource';
        
        $resourcePath = app_path('Support/Inertia/Resources/' . $resourceName . '.php');
        if (!File::exists($resourcePath)) {
            return;
        }
        
        $content = File::get($resourcePath);
        
        // Check if enhanced fields already exist
        if (strpos($content, "first_name") !== false) {
            $this->comment("   ℹ️  Enhanced fields already exist in Customer Resource.");
            return;
        }
        
        // Add imports if needed
        if (strpos($content, "use InertiaResource\\FormFields\\TextField;") === false) {
            $content = str_replace(
                "use InertiaResource\\Columns\\TextColumn;",
                "use InertiaResource\\Columns\\TextColumn;\nuse InertiaResource\\FormFields\\TextField;",
                $content
            );
        }
        
        // Update columns in table() method
        $columnsPattern = "/(TextColumn::make\('id', 'ID'\),)([\s\S]*?)(\/\/ Add your columns here)/";
        $columnsReplacement = '$1' . 
            "\n                TextColumn::make('first_name', 'First Name'),\n" .
            "                TextColumn::make('last_name', 'Last Name'),\n" .
            "                TextColumn::make('email', 'Email'),\n" .
            "                TextColumn::make('mobile_number', 'Mobile Number'),\n" .
            '                $3';
        $content = preg_replace($columnsPattern, $columnsReplacement, $content);
        
        // Update form fields in form() method
        $formPattern = "/(\/\/ Add your form fields here[\s\S]*?)(\]\s*;)/";
        $formReplacement = '$1' . 
            "\n            TextField::make('first_name', 'First Name')->required(),\n" .
            "            TextField::make('last_name', 'Last Name')->required(),\n" .
            "            TextField::make('email', 'Email')->type('email')->required(),\n" .
            "            TextField::make('mobile_number', 'Mobile Number'),\n" .
            '        $2';
        $content = preg_replace($formPattern, $formReplacement, $content);
        
        File::put($resourcePath, $content);
        $this->info("   ✅ Added enhanced fields to Customer Resource.");
    }
}
