<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyComponentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-inertia-resources:copy-components 
                            {--force : Overwrite existing files without prompting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy Vue components from package to application resources/js directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        
        $this->info('📦 Copying Vue components from package...');
        $this->newLine();

        $packagePath = __DIR__.'/../../resources/js';
        $targetPath = resource_path('js');

        if (! File::exists($packagePath)) {
            $this->error('❌ Package Vue files not found at: '.$packagePath);
            return 1;
        }

        $vueFiles = $this->getVueFiles($packagePath);
        
        if (empty($vueFiles)) {
            $this->warn('⚠️  No Vue files found in package');
            return 0;
        }

        $overwritten = 0;
        $created = 0;
        $skipped = 0;

        foreach ($vueFiles as $file) {
            $relativePath = str_replace($packagePath.'/', '', $file);
            $targetFile = $targetPath.'/'.$relativePath;

            // Create directory if it doesn't exist
            $targetDir = dirname($targetFile);
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
                $this->line("   📁 Created directory: {$relativePath}");
            }

            $exists = File::exists($targetFile);

            // Skip if file exists and not forcing
            if ($exists && ! $force) {
                $skipped++;
                continue;
            }

            try {
                File::copy($file, $targetFile);

                if ($exists) {
                    $overwritten++;
                    $this->line("   ✅ Overwritten: {$relativePath}");
                } else {
                    $created++;
                    $this->line("   ✨ Created: {$relativePath}");
                }
            } catch (\Exception $e) {
                $this->warn("   ⚠️  Failed to copy {$relativePath}: ".$e->getMessage());
            }
        }

        $this->newLine();
        
        if ($overwritten > 0 || $created > 0) {
            $this->info('✅ Components copied successfully!');
            $this->newLine();
            
            if ($created > 0) {
                $this->line("   ✨ Created: {$created} file(s)");
            }
            if ($overwritten > 0) {
                $this->line("   ✅ Overwritten: {$overwritten} file(s)");
            }
            if ($skipped > 0) {
                $this->comment("   ⏭️  Skipped: {$skipped} file(s) (use --force to overwrite)");
            }
        } else if ($skipped > 0) {
            $this->comment('💡 All files already exist. Use --force to overwrite them.');
        } else {
            $this->info('✅ Done!');
        }

        return 0;
    }

    /**
     * Get all Vue files recursively
     */
    protected function getVueFiles(string $directory): array
    {
        $files = [];
        $items = File::allFiles($directory);

        foreach ($items as $item) {
            if ($item->getExtension() === 'vue' || $item->getExtension() === 'js') {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }
}

