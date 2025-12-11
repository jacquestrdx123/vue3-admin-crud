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

        // Share menu with Inertia automatically
        $this->shareMenuWithInertia();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \InertiaResource\Console\InstallCommand::class,
                \InertiaResource\Console\ForceReinstallCommand::class,
                \InertiaResource\Console\CreateInertiaResourceCommand::class,
                \InertiaResource\Console\CreateUserModelCommand::class,
                \InertiaResource\Console\PublishAssetsCommand::class,
                \InertiaResource\Console\CreateMenuModelsCommand::class,
                \InertiaResource\Console\RecreateLayoutsCommand::class,
                \InertiaResource\Console\SetupMiddlewareCommand::class,
            ]);
        }

        // Publish config file
        $this->publishes([
            __DIR__.'/../config/inertia-resource.php' => config_path('inertia-resource.php'),
        ], 'inertia-resource-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/create_user_column_preferences_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_user_column_preferences_table.php'),
            __DIR__.'/../database/migrations/create_menu_groups_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', strtotime('+1 second')).'_create_menu_groups_table.php'),
            __DIR__.'/../database/migrations/create_menu_items_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', strtotime('+2 seconds')).'_create_menu_items_table.php'),
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

        // Publish menu models
        $this->publishes([
            __DIR__.'/../stubs/Models/MenuGroup.php.stub' => app_path('Models/MenuGroup.php'),
            __DIR__.'/../stubs/Models/MenuItem.php.stub' => app_path('Models/MenuItem.php'),
        ], 'inertia-resource-menu-models');

        // Publish all assets (config, migrations, components) together
        $this->publishes([
            __DIR__.'/../config/inertia-resource.php' => config_path('inertia-resource.php'),
            __DIR__.'/../database/migrations/create_user_column_preferences_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_create_user_column_preferences_table.php'),
            __DIR__.'/../database/migrations/create_menu_groups_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', strtotime('+1 second')).'_create_menu_groups_table.php'),
            __DIR__.'/../database/migrations/create_menu_items_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', strtotime('+2 seconds')).'_create_menu_items_table.php'),
            __DIR__.'/../resources/js' => resource_path('js/vendor/inertia-resource'),
            __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.js'),
            __DIR__.'/../resources/css/app.css' => resource_path('css/vue-admin-panel.css'),
            __DIR__.'/../stubs/vite.config.js.stub' => base_path('vite.config.js'),
            __DIR__.'/../stubs/Layouts/AdminLayout.vue.stub' => resource_path('js/Layouts/AdminLayout.vue'),
            __DIR__.'/../stubs/Layouts/DashboardLayout.vue.stub' => resource_path('js/Layouts/DashboardLayout.vue'),
            __DIR__.'/../stubs/Pages/Dashboard.vue.stub' => resource_path('js/Pages/Dashboard.vue'),
            __DIR__.'/../stubs/Components/Dashboard/StatCard.vue.stub' => resource_path('js/Components/Dashboard/StatCard.vue'),
            __DIR__.'/../stubs/Models/MenuGroup.php.stub' => app_path('Models/MenuGroup.php'),
            __DIR__.'/../stubs/Models/MenuItem.php.stub' => app_path('Models/MenuItem.php'),
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

        // Register customers provider first
        \Illuminate\Support\Facades\Auth::provider('customers', function ($app) use ($customerModel) {
            return new \Illuminate\Auth\EloquentUserProvider(
                $app['hash'],
                $customerModel
            );
        });

        // Register customer guard with correct signature
        // Auth::extend() receives: $app, $name, $config
        \Illuminate\Support\Facades\Auth::extend('customer', function ($app, $name, array $config) use ($customerModel) {
            // Get the provider from config or use 'customers' as default
            $providerName = $config['provider'] ?? 'customers';
            
            // Create the user provider
            $provider = \Illuminate\Support\Facades\Auth::createUserProvider($providerName);
            
            // If provider doesn't exist, create it with customer model
            if (!$provider) {
                $provider = new \Illuminate\Auth\EloquentUserProvider(
                    $app['hash'],
                    $customerModel
                );
            }

            // Return SessionGuard instance
            return new \Illuminate\Auth\SessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app['request']
            );
        });
    }

    /**
     * Share menu with Inertia automatically if models exist
     */
    protected function shareMenuWithInertia(): void
    {
        // Only share menu in web requests, not console
        if ($this->app->runningInConsole()) {
            return;
        }

        // Check if Inertia facade is available
        if (!class_exists(\Inertia\Inertia::class)) {
            return;
        }

        // Check if HandleInertiaRequests middleware exists
        // If it does, it will handle menu sharing via its share() method
        // If not, we'll use Inertia::share() as a fallback
        $middlewareClass = \App\Http\Middleware\HandleInertiaRequests::class;
        if (class_exists($middlewareClass)) {
            // Middleware exists, it will handle menu sharing
            // No need to share here as middleware takes precedence
            return;
        }

        // Fallback: Share menu using Inertia::share() if middleware doesn't exist
        try {
            \Inertia\Inertia::share('menu', function () {
                $menuGroupModel = config('inertia-resource.menu_group_model', \App\Models\MenuGroup::class);
                $menuItemModel = config('inertia-resource.menu_item_model', \App\Models\MenuItem::class);

                // Check if models exist
                if (!class_exists($menuGroupModel) || !class_exists($menuItemModel)) {
                    return [];
                }

                // Use MenuBuilder to build menu
                return \InertiaResource\Inertia\MenuBuilder::build();
            });
        } catch (\Exception $e) {
            // Silently fail if Inertia is not properly set up
            // This prevents errors during installation or when Inertia is not configured
        }
    }
}

