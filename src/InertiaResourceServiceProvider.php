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
                \InertiaResource\Console\CreateInertiaResourceCommand::class,
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
            __DIR__.'/../resources/js' => resource_path('js/vendor/inertia-resource'),
        ], 'inertia-resource-components');

        // Publish Tailwind config
        $this->publishes([
            __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.js'),
        ], 'inertia-resource-tailwind');

        // Publish CSS file
        $this->publishes([
            __DIR__.'/../resources/css/app.css' => resource_path('css/vue-admin-panel.css'),
        ], 'inertia-resource-assets');

        // Publish login page stubs
        $this->publishes([
            __DIR__.'/../stubs/Pages/Auth/AdminLogin.vue.stub' => resource_path('js/Pages/Auth/AdminLogin.vue'),
            __DIR__.'/../stubs/Pages/Auth/CustomerLogin.vue.stub' => resource_path('js/Pages/Auth/CustomerLogin.vue'),
        ], 'inertia-resource-login-pages');

        // Publish all assets (config, migrations, components) together
        $this->publishes([
            __DIR__.'/../config/inertia-resource.php' => config_path('inertia-resource.php'),
            __DIR__.'/../database/migrations/create_user_column_preferences_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_user_column_preferences_table.php'),
            __DIR__.'/../resources/js' => resource_path('js/vendor/inertia-resource'),
            __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.js'),
            __DIR__.'/../resources/css/app.css' => resource_path('css/vue-admin-panel.css'),
        ], 'inertia-resource');
    }
}

