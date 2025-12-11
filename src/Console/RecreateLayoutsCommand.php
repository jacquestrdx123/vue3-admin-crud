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
    protected $signature = 'vue-admin-panel:recreate-layouts 
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate Vue Admin Panel layout files (AdminLayout, DashboardLayout, Dashboard page, StatCard)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('📐 Recreating Vue Admin Panel layout files...');
        $this->newLine();

        $layoutsPath = resource_path('js/Layouts');
        $pagesPath = resource_path('js/Pages');
        $componentsPath = resource_path('js/Components/Dashboard');

        // Create directories if they don't exist
        if (!File::exists($layoutsPath)) {
            File::makeDirectory($layoutsPath, 0755, true);
            $this->info('✅ Created Layouts directory');
        }

        if (!File::exists($pagesPath)) {
            File::makeDirectory($pagesPath, 0755, true);
            $this->info('✅ Created Pages directory');
        }

        if (!File::exists($componentsPath)) {
            File::makeDirectory($componentsPath, 0755, true);
            $this->info('✅ Created Components/Dashboard directory');
        }

        // Files to recreate from vendor (copying to app directory to override vendor versions)
        $vendorPath = resource_path('js/vendor/inertia-resource');
        $files = [
            [
                'source' => $vendorPath.'/Layouts/AdminLayout.vue',
                'target' => $layoutsPath.'/AdminLayout.vue',
                'name' => 'AdminLayout.vue',
            ],
            [
                'source' => $vendorPath.'/Layouts/DashboardLayout.vue',
                'target' => $layoutsPath.'/DashboardLayout.vue',
                'name' => 'DashboardLayout.vue',
            ],
            [
                'source' => $vendorPath.'/Pages/Dashboard.vue',
                'target' => $pagesPath.'/Dashboard.vue',
                'name' => 'Dashboard.vue',
            ],
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
            if (!File::exists($file['source'])) {
                $this->warn("⚠️  Vendor file not found: {$file['source']}");
                $this->comment("   Make sure to run: php artisan vendor:publish --tag=inertia-resource-components");
                continue;
            }

            $exists = File::exists($file['target']);

            if ($exists && !$force) {
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
                $this->error("   ❌ Failed to create {$file['name']}: " . $e->getMessage());
            }
        }

        $this->newLine();
        
        if ($created > 0 || $overwritten > 0) {
            $this->info("✅ Layout files recreation complete!");
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
