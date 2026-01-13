<?php

namespace InertiaResource\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

class SyncPermissionsFromPoliciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-inertia-resources:permissions:sync-from-policies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from Policy files to the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning Policy files...');

        $policyFiles = File::allFiles(app_path('Policies'));

        $permissionsFound = [];

        foreach ($policyFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());

            // Extract permission names from $user->can('...') calls
            preg_match_all("/->can\(['\"]([^'\"]+)['\"]\)/", $content, $matches);

            if (! empty($matches[1])) {
                foreach ($matches[1] as $permissionName) {
                    $permissionsFound[] = $permissionName;
                }
            }
        }

        $permissionsFound = array_unique($permissionsFound);
        sort($permissionsFound);

        $this->info('Found '.count($permissionsFound).' unique permissions in Policy files.');

        // Sync permissions to database
        $created = [];
        $existing = [];

        foreach ($permissionsFound as $permissionName) {
            $permission = Permission::firstOrCreate(
                [
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]
            );

            if ($permission->wasRecentlyCreated) {
                $created[] = $permissionName;
            } else {
                $existing[] = $permissionName;
            }
        }

        // Find and remove orphaned permissions
        $allDbPermissions = Permission::where('guard_name', 'web')->get();
        $orphaned = [];

        foreach ($allDbPermissions as $dbPermission) {
            if (! in_array($dbPermission->name, $permissionsFound)) {
                $dbPermission->delete();
                $orphaned[] = $dbPermission->name;
            }
        }

        // Output results
        if (! empty($created)) {
            $this->info('Created '.count($created).' new permissions:');
            foreach ($created as $name) {
                $this->line("  - {$name}");
            }
        }

        if (! empty($existing)) {
            $this->info('Found '.count($existing).' existing permissions.');
        }

        if (! empty($orphaned)) {
            $this->warn('Removed '.count($orphaned).' orphaned permissions:');
            foreach ($orphaned as $name) {
                $this->line("  - {$name}");
            }
        }

        if (empty($created) && empty($orphaned)) {
            $this->info('All permissions are in sync. No changes made.');
        } else {
            $this->info('Permissions synced successfully!');
        }

        return self::SUCCESS;
    }
}
