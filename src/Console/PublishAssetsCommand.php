<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;

class PublishAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-admin-panel:publish 
                            {--force : Overwrite existing files}
                            {--tag= : The tag that has the assets you want to publish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Republish Vue Admin Panel assets after package update';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');
        $tag = $this->option('tag') ?: 'inertia-resource';

        $this->info('📁 Republishing Vue Admin Panel assets...');
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

        $this->newLine();
        $this->info('✅ Assets republished successfully!');
        $this->newLine();

        if (!$force) {
            $this->comment('Note: If files already exist, they were not overwritten.');
            $this->comment('Use --force to overwrite existing files with the latest versions.');
        }

        return 0;
    }
}

