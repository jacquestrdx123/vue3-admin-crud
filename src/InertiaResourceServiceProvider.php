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
        // Register customer guard if customers are enabled
        $this->registerCustomerGuard();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \InertiaResource\Console\InstallCommand::class,
                \InertiaResource\Console\CreateInertiaResourceCommand::class,
                \InertiaResource\Console\CreateUserModelCommand::class,
                \InertiaResource\Console\PublishAssetsCommand::class,
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

        // Publish vite.config.js stub
        $this->publishes([
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
        ], 'inertia-resource-vite');

        // Publish CSS file
        $this->publishes([
            __DIR__.'/../resources/css/app.css' => resource_path('css/vue-admin-panel.css'),
        ], 'inertia-resource-assets');

        // Publish login page stubs
        $this->publishes([
            __DIR__.'/../stubs/Pages/Auth/AdminLogin.vue.stub' => resource_path('js/Pages/Auth/AdminLogin.vue'),
            __DIR__.'/../stubs/Pages/Auth/CustomerLogin.vue.stub' => resource_path('js/Pages/Auth/CustomerLogin.vue'),
        ], 'inertia-resource-login-pages');

        // Publish admin layouts and dashboard
        $this->publishes([
            __DIR__.'/../stubs/Layouts/AdminLayout.vue.stub' => resource_path('js/Layouts/AdminLayout.vue'),
            __DIR__.'/../stubs/Layouts/DashboardLayout.vue.stub' => resource_path('js/Layouts/DashboardLayout.vue'),
            __DIR__.'/../stubs/Pages/Dashboard.vue.stub' => resource_path('js/Pages/Dashboard.vue'),
            __DIR__.'/../stubs/Components/Dashboard/StatCard.vue.stub' => resource_path('js/Components/Dashboard/StatCard.vue'),
        ], 'inertia-resource-layouts');

        // Publish all assets (config, migrations, components) together
        $this->publishes([
            __DIR__.'/../config/inertia-resource.php' => config_path('inertia-resource.php'),
            __DIR__.'/../database/migrations/create_user_column_preferences_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_user_column_preferences_table.php'),
            __DIR__.'/../resources/js' => resource_path('js/vendor/inertia-resource'),
            __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.js'),
            __DIR__.'/../resources/css/app.css' => resource_path('css/vue-admin-panel.css'),
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
            __DIR__.'/../stubs/Layouts/AdminLayout.vue.stub' => resource_path('js/Layouts/AdminLayout.vue'),
            __DIR__.'/../stubs/Layouts/DashboardLayout.vue.stub' => resource_path('js/Layouts/DashboardLayout.vue'),
            __DIR__.'/../stubs/Pages/Dashboard.vue.stub' => resource_path('js/Pages/Dashboard.vue'),
            __DIR__.'/../stubs/Components/Dashboard/StatCard.vue.stub' => resource_path('js/Components/Dashboard/StatCard.vue'),
        ], 'inertia-resource');
    }

    /**
     * Register customer guard programmatically
     */
    protected function registerCustomerGuard(): void
    {
        $useCustomers = config('inertia-resource.use_customers', false);
        
        if (!$useCustomers) {
            return;
        }

        $customerModel = config('inertia-resource.customer_model');
        
        if (!$customerModel) {
            return;
        }

        // Handle class constant format
        if (is_string($customerModel) && strpos($customerModel, '::class') !== false) {
            $customerModel = str_replace('::class', '', $customerModel);
        }

        // Ensure the model class exists
        if (!class_exists($customerModel)) {
            return;
        }

        // Register customers provider if not already registered
        $auth = $this->app->make('auth');
        
        $auth->provider('customers', function ($app) use ($customerModel) {
            return new \Illuminate\Auth\EloquentUserProvider(
                $app['hash'],
                $customerModel
            );
        });

        // Register customer guard if not already registered
        // Check if guard exists by trying to resolve it
        try {
            $auth->guard('customer');
        } catch (\InvalidArgumentException $e) {
            // Guard doesn't exist, register it
            $auth->extend('customer', function ($app) use ($customerModel, $auth) {
                $provider = $auth->createUserProvider('customers');
                
                if (!$provider) {
                    // Create the provider if it doesn't exist
                    $provider = $auth->createUserProvider([
                        'driver' => 'eloquent',
                        'model' => $customerModel,
                    ]);
                }

                return new \Illuminate\Auth\SessionGuard(
                    'customer',
                    $provider,
                    $app['session.store'],
                    $app['request']
                );
            });
        }
    }
}

