<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateUserModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-admin-panel:make-user {--force : Overwrite existing User model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a User model with all fields and password confirmation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modelPath = app_path('Models/User.php');
        $force = $this->option('force');

        // Check if User model already exists
        if (File::exists($modelPath) && !$force) {
            if (!$this->confirm('User model already exists. Overwrite?', false)) {
                $this->warn('Skipped User model creation.');
                return 0;
            }
        }

        // Ensure Models directory exists
        $modelsPath = app_path('Models');
        if (!File::exists($modelsPath)) {
            File::makeDirectory($modelsPath, 0755, true);
        }

        // Get the app namespace
        $appNamespace = $this->getAppNamespace();

        // Generate User model
        $stub = File::get(__DIR__.'/../../stubs/UserModel.stub');
        $stub = str_replace('{{ namespace }}', $appNamespace, $stub);

        File::put($modelPath, $stub);
        $this->info('✅ Created User model at '.$modelPath);

        // Check if migration exists
        $migrationPath = database_path('migrations');
        $migrations = File::glob($migrationPath.'/*_create_users_table.php');
        
        if (empty($migrations)) {
            $this->warn('⚠️  No users table migration found.');
            $this->comment('   Run: php artisan make:migration create_users_table');
        }

        $this->newLine();
        $this->info('✅ User model created successfully!');
        $this->newLine();
        $this->comment('The User model includes:');
        $this->comment('  - Standard authentication fields (name, email, password)');
        $this->comment('  - Password confirmation field');
        $this->comment('  - Email verification support');
        $this->comment('  - Remember token support');
        $this->comment('  - Proper fillable, hidden, and casts arrays');
        $this->newLine();

        return 0;
    }

    /**
     * Get the application namespace
     */
    protected function getAppNamespace(): string
    {
        $composer = json_decode(File::get(base_path('composer.json')), true);
        
        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath(app_path()) === realpath(base_path($pathChoice))) {
                    return $namespace;
                }
            }
        }

        return 'App\\';
    }
}

