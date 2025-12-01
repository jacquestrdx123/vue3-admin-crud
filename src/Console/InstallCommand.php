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
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Installing Vue Admin Panel...');
        $this->newLine();

        // Ask if user wants to use Customers
        $useCustomers = $this->confirm('Do you want to use Customers?', false);
        $this->updateCustomersConfig($useCustomers);
        $this->newLine();

        // Merge package.json dependencies
        $this->info('📦 Merging npm dependencies...');
        $this->mergePackageJson();
        $this->newLine();

        // Publish Tailwind config
        $this->info('🎨 Publishing Tailwind configuration...');
        $this->publishTailwindConfig();
        $this->newLine();

        // Publish all assets
        $this->info('📁 Publishing package assets...');
        $this->call('vendor:publish', [
            '--tag' => 'inertia-resource',
            '--force' => false,
        ]);
        $this->newLine();

        // Create Inertia root template
        $this->info('📄 Creating Inertia root template...');
        $this->createInertiaRootTemplate();
        $this->newLine();

        // Create JavaScript entry point
        $this->info('📜 Creating JavaScript entry point...');
        $this->createJavaScriptEntryPoint();
        $this->newLine();

        // Create login pages
        $this->info('🔐 Creating login pages...');
        $this->createLoginPages();
        $this->newLine();

        // Create admin routes
        $this->info('🛣️  Creating admin routes...');
        $this->createAdminRoutes();
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
                $this->warn('app.js already exists. Skipping...');
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
        $pagesPath = resource_path('js/Pages');
        $authPath = $pagesPath.'/Auth';

        // Create Auth directory if it doesn't exist
        if (!File::exists($authPath)) {
            File::makeDirectory($authPath, 0755, true);
        }

        // Admin Login Page
        $adminLoginStub = __DIR__.'/../../stubs/Pages/Auth/AdminLogin.vue.stub';
        $adminLoginPath = $authPath.'/AdminLogin.vue';

        if (File::exists($adminLoginStub)) {
            if (File::exists($adminLoginPath)) {
                $this->warn('AdminLogin.vue already exists. Skipping...');
            } else {
                File::copy($adminLoginStub, $adminLoginPath);
                $this->info('Created AdminLogin.vue');
            }
        }

        // Customer Login Page (only if use_customers is enabled)
        $useCustomers = config('inertia-resource.use_customers', false);
        
        if ($useCustomers) {
            $customerLoginStub = __DIR__.'/../../stubs/Pages/Auth/CustomerLogin.vue.stub';
            $customerLoginPath = $authPath.'/CustomerLogin.vue';

            if (File::exists($customerLoginStub)) {
                if (File::exists($customerLoginPath)) {
                    $this->warn('CustomerLogin.vue already exists. Skipping...');
                } else {
                    File::copy($customerLoginStub, $customerLoginPath);
                    $this->info('Created CustomerLogin.vue');
                }
            }
        } else {
            $this->comment('Customer login page skipped (use_customers is disabled).');
            $this->comment('Enable it in config/inertia-resource.php to create the customer login page.');
        }
    }

    /**
     * Create admin routes
     */
    protected function createAdminRoutes(): void
    {
        $routesPath = base_path('routes');
        $routesFile = "{$routesPath}/web.php";

        // Check if routes file exists
        if (!File::exists($routesFile)) {
            $this->warn('⚠️  Could not find routes/web.php file.');
            $this->comment('Please add the following routes manually:');
            $this->displayAdminRoutes();
            return;
        }

        $routesContent = File::get($routesFile);
        
        // Check if admin routes already exist
        if (strpos($routesContent, "Route::prefix('admin')") !== false || 
            strpos($routesContent, "Route::get('/admin/login'") !== false ||
            strpos($routesContent, "->name('admin.login')") !== false) {
            $this->warn('⚠️  Admin routes already exist in routes file. Skipping route generation.');
            return;
        }

        // Check if Inertia is imported
        $hasInertiaImport = strpos($routesContent, "use Inertia\\Inertia;") !== false || 
                            strpos($routesContent, "use Inertia\\Inertia as Inertia;") !== false;

        // Prepare admin routes
        $adminRoutes = "\n\n// Admin routes\n";
        $adminRoutes .= "Route::prefix('admin')->name('admin.')->group(function () {\n";
        $adminRoutes .= "    // Login routes (guest middleware)\n";
        $adminRoutes .= "    Route::middleware(['guest'])->group(function () {\n";
        $adminRoutes .= "        Route::get('/login', function () {\n";
        $adminRoutes .= "            return \\Inertia\\Inertia::render('Auth/AdminLogin');\n";
        $adminRoutes .= "        })->name('login');\n";
        $adminRoutes .= "        \n";
        $adminRoutes .= "        Route::post('/login', function (\\Illuminate\\Http\\Request \$request) {\n";
        $adminRoutes .= "            \$credentials = \$request->validate([\n";
        $adminRoutes .= "                'email' => ['required', 'email'],\n";
        $adminRoutes .= "                'password' => ['required'],\n";
        $adminRoutes .= "            ]);\n";
        $adminRoutes .= "            \n";
        $adminRoutes .= "            if (\\Illuminate\\Support\\Facades\\Auth::attempt(\$credentials, \$request->boolean('remember'))) {\n";
        $adminRoutes .= "                \$request->session()->regenerate();\n";
        $adminRoutes .= "                return redirect()->intended('/admin');\n";
        $adminRoutes .= "            }\n";
        $adminRoutes .= "            \n";
        $adminRoutes .= "            return back()->withErrors([\n";
        $adminRoutes .= "                'email' => 'The provided credentials do not match our records.',\n";
        $adminRoutes .= "            ])->onlyInput('email');\n";
        $adminRoutes .= "        })->name('login');\n";
        $adminRoutes .= "    });\n";
        $adminRoutes .= "});\n";

        // Add Inertia import if not present
        if (!$hasInertiaImport) {
            // Find the last use statement or add after <?php
            if (preg_match('/^<\?php\s*\n/', $routesContent)) {
                $routesContent = preg_replace(
                    '/^(<\?php\s*\n)/',
                    "$1use Inertia\\Inertia;\n",
                    $routesContent
                );
            } else {
                // Add after first line
                $routesContent = preg_replace(
                    '/^(<\?php[^\n]*\n)/',
                    "$1use Inertia\\Inertia;\n",
                    $routesContent
                );
            }
        }

        // Append admin routes to file
        $routesContent .= $adminRoutes;
        File::put($routesFile, $routesContent);
        $this->info('✅ Added admin routes to routes/web.php');
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
        $this->line("                return redirect()->intended('/admin');");
        $this->line("            }");
        $this->line("            ");
        $this->line("            return back()->withErrors([");
        $this->line("                'email' => 'The provided credentials do not match our records.',");
        $this->line("            ])->onlyInput('email');");
        $this->line("        })->name('login');");
        $this->line("    });");
        $this->line("});");
        $this->newLine();
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
}
