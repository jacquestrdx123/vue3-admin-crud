<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupMiddlewareCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-admin-panel:setup-middleware 
                            {--force : Overwrite existing middleware file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and register HandleInertiaRequests middleware for Vue Admin Panel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('🔧 Setting up HandleInertiaRequests middleware...');
        $this->newLine();

        // Step 1: Create middleware file
        $this->createMiddleware($force);

        // Step 2: Register middleware in bootstrap/app.php
        $this->registerMiddleware();

        $this->newLine();
        $this->info('✅ Middleware setup complete!');
        $this->newLine();

        return 0;
    }

    /**
     * Create HandleInertiaRequests middleware file
     */
    protected function createMiddleware(bool $force): void
    {
        $middlewarePath = app_path('Http/Middleware');
        
        if (!File::exists($middlewarePath)) {
            File::makeDirectory($middlewarePath, 0755, true);
            $this->info('✅ Created Http/Middleware directory');
        }
        
        $handleInertiaPath = "{$middlewarePath}/HandleInertiaRequests.php";
        $stubPath = __DIR__.'/../../stubs/Middleware/HandleInertiaRequests.stub';
        
        if (!File::exists($stubPath)) {
            $this->error("❌ HandleInertiaRequests.stub not found at: {$stubPath}");
            return;
        }

        $exists = File::exists($handleInertiaPath);

        if ($exists && !$force) {
            $this->comment('   ⏭️  HandleInertiaRequests middleware already exists (use --force to overwrite)');
            return;
        }

        try {
            $stub = File::get($stubPath);
            File::put($handleInertiaPath, $stub);
            
            if ($exists) {
                $this->info('   ✅ Overwritten HandleInertiaRequests.php');
            } else {
                $this->info('   ✅ Created HandleInertiaRequests.php');
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Failed to create middleware: " . $e->getMessage());
        }
    }

    /**
     * Register HandleInertiaRequests middleware in bootstrap/app.php
     */
    protected function registerMiddleware(): void
    {
        $bootstrapAppPath = base_path('bootstrap/app.php');
        
        if (!File::exists($bootstrapAppPath)) {
            $this->warn('⚠️  bootstrap/app.php not found. Please register HandleInertiaRequests middleware manually.');
            $this->line('   Add this to your middleware configuration:');
            $this->line('   $middleware->web(append: [\\App\\Http\\Middleware\\HandleInertiaRequests::class]);');
            return;
        }
        
        $content = File::get($bootstrapAppPath);
        
        // Check if middleware is already registered
        if (strpos($content, 'HandleInertiaRequests') !== false) {
            $this->comment('   ✓ HandleInertiaRequests middleware already registered in bootstrap/app.php');
            return;
        }
        
        // Check if withMiddleware block exists
        $middlewarePattern = '/->withMiddleware\s*\(\s*function\s*\(Middleware\s+\$middleware\):\s*void\s*\{([^}]*)\}\s*\)/s';
        
        if (preg_match($middlewarePattern, $content, $matches)) {
            $middlewareBlock = $matches[1];
            
            // Check if it's empty (just whitespace/comments)
            if (trim($middlewareBlock) === '' || trim($middlewareBlock) === '//') {
                // Empty block - add middleware registration
                $replacement = "->withMiddleware(function (Middleware \$middleware): void {\n        \$middleware->web(append: [\n            \\App\\Http\\Middleware\\HandleInertiaRequests::class,\n        ]);\n    })";
                $content = preg_replace($middlewarePattern, $replacement, $content);
            } else {
                // Has content - check if web() call exists
                if (strpos($middlewareBlock, '->web(') !== false) {
                    // Append to existing web() call
                    if (preg_match('/(\$middleware->web\([^)]*)\)/', $content, $webMatches)) {
                        // Check if HandleInertiaRequests is already in the array
                        if (strpos($webMatches[0], 'HandleInertiaRequests') === false) {
                            $content = preg_replace(
                                '/(\$middleware->web\([^)]*)\)/',
                                '$1, \\App\\Http\\Middleware\\HandleInertiaRequests::class)',
                                $content
                            );
                        }
                    }
                } else {
                    // Add new web() call
                    $replacement = "->withMiddleware(function (Middleware \$middleware): void {\n        \$middleware->web(append: [\n            \\App\\Http\\Middleware\\HandleInertiaRequests::class,\n        ]);\n{$middlewareBlock}\n    })";
                    $content = preg_replace($middlewarePattern, $replacement, $content);
                }
            }
            
            File::put($bootstrapAppPath, $content);
            $this->info('   ✅ Registered HandleInertiaRequests middleware in bootstrap/app.php');
        } else {
            $this->warn('⚠️  Could not find withMiddleware block in bootstrap/app.php.');
            $this->line('   Please register HandleInertiaRequests middleware manually:');
            $this->line('   $middleware->web(append: [\\App\\Http\\Middleware\\HandleInertiaRequests::class]);');
        }
    }
}
