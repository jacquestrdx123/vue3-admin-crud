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
            
            $needsFix = true;
        }
        
        if ($needsFix && $fixedContent !== $viteConfigContent) {
            File::put($viteConfigPath, $fixedContent);
            $this->info('✅ Fixed vite.config.js import and configuration.');
        } elseif (str_contains($viteConfigContent, "from 'laravel-vite-plugin'") || 
                   str_contains($viteConfigContent, 'from "laravel-vite-plugin"')) {
            $this->comment('✓ vite.config.js already has correct import.');
        }
    }
}
