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
        
        // Check for incorrect import: import laravel from 'laravel-vite-plugin';
        if (str_contains($viteConfigContent, "from 'laravel-vite-plugin'") || 
            str_contains($viteConfigContent, 'from "laravel-vite-plugin"')) {
            
            $this->warn('⚠️  Found incorrect import in vite.config.js: laravel-vite-plugin');
            $this->comment('   The correct import should be: laravel/vite-plugin');
            
            // Fix the import
            $fixedContent = preg_replace(
                "/import\s+laravel\s+from\s+['\"]laravel-vite-plugin['\"]/",
                "import laravel from 'laravel/vite-plugin'",
                $viteConfigContent
            );
            
            if ($fixedContent !== $viteConfigContent) {
                File::put($viteConfigPath, $fixedContent);
                $this->info('✅ Fixed vite.config.js import statement.');
            } else {
                $this->warn('⚠️  Could not automatically fix vite.config.js. Please update manually:');
                $this->comment('   Change: import laravel from \'laravel-vite-plugin\';');
                $this->comment('   To:     import laravel from \'laravel/vite-plugin\';');
            }
        } elseif (str_contains($viteConfigContent, 'laravel/vite-plugin')) {
            $this->comment('✓ vite.config.js already has correct import.');
        }
    }
}
