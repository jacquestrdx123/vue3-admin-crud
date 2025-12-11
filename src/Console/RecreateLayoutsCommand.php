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

        // Files to recreate
        $files = [
            [
                'stub' => __DIR__.'/../../stubs/Layouts/AdminLayout.vue.stub',
                'target' => $layoutsPath.'/AdminLayout.vue',
                'name' => 'AdminLayout.vue',
            ],
            [
                'stub' => __DIR__.'/../../stubs/Layouts/DashboardLayout.vue.stub',
                'target' => $layoutsPath.'/DashboardLayout.vue',
                'name' => 'DashboardLayout.vue',
            ],
            [
                'stub' => __DIR__.'/../../stubs/Pages/Dashboard.vue.stub',
                'target' => $pagesPath.'/Dashboard.vue',
                'name' => 'Dashboard.vue',
            ],
            [
                'stub' => __DIR__.'/../../stubs/Components/Dashboard/StatCard.vue.stub',
                'target' => $componentsPath.'/StatCard.vue',
                'name' => 'StatCard.vue',
            ],
        ];

        $created = 0;
        $skipped = 0;
        $overwritten = 0;

        foreach ($files as $file) {
            if (!File::exists($file['stub'])) {
                $this->warn("⚠️  Stub not found: {$file['stub']}");
                continue;
            }

            $exists = File::exists($file['target']);

            if ($exists && !$force) {
                $this->comment("   ⏭️  Skipped {$file['name']} (already exists, use --force to overwrite)");
                $skipped++;
                continue;
            }

            try {
                File::copy($file['stub'], $file['target']);
                
                if ($exists) {
                    $this->info("   ✅ Overwritten {$file['name']}");
                    $overwritten++;
                } else {
                    $this->info("   ✅ Created {$file['name']}");
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
