<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RecreateLayoutsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-inertia-resources:recreate-layouts 
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate Vue Inertia Resources component files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('📐 Recreating Vue Inertia Resources component files...');
        $this->newLine();

        $componentsPath = resource_path('js/Components/Dashboard');

        // Create directories if they don't exist
        if (! File::exists($componentsPath)) {
            File::makeDirectory($componentsPath, 0755, true);
            $this->info('✅ Created Components/Dashboard directory');
        }

        // Files to recreate from vendor (copying to app directory to override vendor versions)
        $vendorPath = resource_path('js/vendor/inertia-resource');
        $files = [
            [
                'source' => $vendorPath.'/Components/Dashboard/StatCard.vue',
                'target' => $componentsPath.'/StatCard.vue',
                'name' => 'StatCard.vue',
            ],
        ];

        $created = 0;
        $skipped = 0;
        $overwritten = 0;

        foreach ($files as $file) {
            if (! File::exists($file['source'])) {
                $this->warn("⚠️  Vendor file not found: {$file['source']}");
                $this->comment('   Make sure to run: php artisan vendor:publish --tag=inertia-resource-components');

                continue;
            }

            $exists = File::exists($file['target']);

            if ($exists && ! $force) {
                $this->comment("   ⏭️  Skipped {$file['name']} (already exists, use --force to overwrite)");
                $skipped++;

                continue;
            }

            try {
                File::copy($file['source'], $file['target']);

                if ($exists) {
                    $this->info("   ✅ Overwritten {$file['name']} (copied from vendor)");
                    $overwritten++;
                } else {
                    $this->info("   ✅ Created {$file['name']} (copied from vendor)");
                    $created++;
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to create {$file['name']}: ".$e->getMessage());
            }
        }

        $this->newLine();

        if ($created > 0 || $overwritten > 0) {
            $this->info('✅ Component files recreation complete!');
            if ($created > 0) {
                $this->line("   Created: {$created} file(s)");
            }
            if ($overwritten > 0) {
                $this->line("   Overwritten: {$overwritten} file(s)");
            }
        }

        if ($skipped > 0) {
            $this->comment("   Skipped: {$skipped} file(s) (use --force to overwrite)");
        }

        $this->newLine();

        return 0;
    }
}
