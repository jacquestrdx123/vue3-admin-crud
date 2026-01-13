<?php

namespace InertiaResource;

use Illuminate\Support\ServiceProvider;
use InertiaResource\Contracts\ColumnPreferenceRepository;
use InertiaResource\Models\UserColumnPreference;
use InertiaResource\Models\UserColumnPreferenceRepository;

class InertiaResourceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/inertia-resource.php',
            'inertia-resource'
        );

        // Bind ColumnPreferenceRepository if model is configured
        $preferenceModel = config('inertia-resource.column_preference_model');
        if ($preferenceModel && class_exists($preferenceModel)) {
            $this->app->singleton(ColumnPreferenceRepository::class, function () use ($preferenceModel) {
                return new UserColumnPreferenceRepository($preferenceModel);
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \InertiaResource\Console\InstallCommand::class,
                \InertiaResource\Console\UninstallCommand::class,
                \InertiaResource\Console\ForceReinstallCommand::class,
                \InertiaResource\Console\CreateInertiaResourceCommand::class,
                \InertiaResource\Console\CreateUserModelCommand::class,
                \InertiaResource\Console\PublishAssetsCommand::class,
                \InertiaResource\Console\CopyComponentsCommand::class,
                \InertiaResource\Console\RecreateLayoutsCommand::class,
                \InertiaResource\Console\SetupMiddlewareCommand::class,
                \InertiaResource\Console\SyncPermissionsFromPoliciesCommand::class,
            ]);
        }

        // Publish config file
        $this->publishes([
            __DIR__.'/../config/inertia-resource.php' => config_path('inertia-resource.php'),
        ], 'inertia-resource-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/create_user_column_preferences_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_user_column_preferences_table.php'),
        ], 'inertia-resource-migrations');

        // Publish Vue components, pages, and composables
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js'),
        ], 'inertia-resource-components');

        // Publish Tailwind config
        $this->publishes([
            __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.js'),
        ], 'inertia-resource-tailwind');

        // Publish vite.config.js stub
        $this->publishes([
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
        ], 'inertia-resource-vite');

        // Publish CSS file
        $this->publishes([
            __DIR__.'/../resources/css/app.css' => resource_path('css/vue-inertia-resources.css'),
        ], 'inertia-resource-assets');

        // Note: Login pages, layouts, dashboard, and components are now published via inertia-resource-components
        // They are located in resources/js/ and will be published directly to js/
        // Users can override by creating files in their app's resources/js/ directory

        // Publish all assets (config, migrations, components) together
        // Note: Layouts, pages, and components are now in resources/js/ and published directly to js/
        $this->publishes([
            __DIR__.'/../config/inertia-resource.php' => config_path('inertia-resource.php'),
            __DIR__.'/../database/migrations/create_user_column_preferences_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_user_column_preferences_table.php'),
            __DIR__.'/../resources/js' => resource_path('js'),
            __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.js'),
            __DIR__.'/../resources/css/app.css' => resource_path('css/vue-inertia-resources.css'),
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
        ], 'inertia-resource');
    }
}

