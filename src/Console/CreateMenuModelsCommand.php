<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateMenuModelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-admin-panel:create-menu-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create MenuGroup and MenuItem models and migrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Creating menu models and migrations...');
        $this->newLine();

        // Check if models already exist
        $menuGroupPath = app_path('Models/MenuGroup.php');
        $menuItemPath = app_path('Models/MenuItem.php');

        if (File::exists($menuGroupPath) || File::exists($menuItemPath)) {
            if (!$this->confirm('Menu models already exist. Do you want to overwrite them?', false)) {
                $this->info('Skipping model creation.');
                return 0;
            }
        }

        // Publish migrations
        $this->info('📦 Publishing migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'inertia-resource-migrations',
            '--force' => false,
        ]);
        $this->newLine();

        // Publish models
        $this->info('📄 Publishing models...');
        $this->call('vendor:publish', [
            '--tag' => 'inertia-resource-menu-models',
            '--force' => true,
        ]);
        $this->newLine();

        $this->info('✅ Menu models and migrations created successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Run migrations: php artisan migrate');
        $this->line('  2. Seed your menu data in the database');
        $this->line('  3. Use MenuBuilder::build() to share menu data with Inertia');
        $this->newLine();

        return 0;
    }
}

