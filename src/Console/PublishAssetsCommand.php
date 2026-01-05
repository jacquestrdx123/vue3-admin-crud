<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-inertia-resources:publish 
                            {--force : Overwrite existing files}
                            {--tag= : The tag that has the assets you want to publish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Republish Vue Inertia Resources assets after package update';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $tag = $this->option('tag') ?: 'inertia-resource';

        $this->info('📁 Republishing Vue Inertia Resources assets...');
        $this->newLine();

        $options = [
            '--tag' => $tag,
        ];

        if ($force) {
            $options['--force'] = true;
            $this->warn('⚠️  Force mode enabled - existing files will be overwritten!');
            $this->newLine();
        } else {
            $this->comment('💡 Tip: Use --force to overwrite existing files');
            $this->newLine();
        }

        $this->call('vendor:publish', $options);

        // Always overwrite Vue files by default
        $this->newLine();
        $this->info('🔄 Overwriting Vue.js files...');
        $this->overwriteVueFiles();

        $this->newLine();
        $this->info('✅ Assets republished successfully!');
        $this->newLine();

        if (! $force) {
            $this->comment('Note: Vue.js files were automatically overwritten.');
            $this->comment('Other files: If they already exist, they were not overwritten.');
            $this->comment('Use --force to overwrite all existing files with the latest versions.');
        }

        return 0;
    }

    /**
     * Overwrite Vue.js files from package to published location
     */
    protected function overwriteVueFiles(): void
    {
        $packagePath = __DIR__.'/../../resources/js';
        $targetPath = resource_path('js');

        if (! File::exists($packagePath)) {
            $this->warn('⚠️  Package Vue files not found');
            return;
        }

        $vueFiles = $this->getVueFiles($packagePath);
        $overwritten = 0;
        $created = 0;

        foreach ($vueFiles as $file) {
            $relativePath = str_replace($packagePath.'/', '', $file);
            $targetFile = $targetPath.'/'.$relativePath;

            // Create directory if it doesn't exist
            $targetDir = dirname($targetFile);
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            try {
                $exists = File::exists($targetFile);
                File::copy($file, $targetFile);

                if ($exists) {
                    $overwritten++;
                } else {
                    $created++;
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Failed to copy {$relativePath}: ".$e->getMessage());
            }
        }

        if ($overwritten > 0 || $created > 0) {
            if ($overwritten > 0) {
                $this->line("   ✅ Overwritten {$overwritten} Vue file(s)");
            }
            if ($created > 0) {
                $this->line("   ✅ Created {$created} Vue file(s)");
            }
        }
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
