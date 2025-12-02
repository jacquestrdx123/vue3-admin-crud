<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of your User model.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Use Customers
    |--------------------------------------------------------------------------
    |
    | Enable customer functionality. When enabled, a customer login page will be
    | available and customers can access the customer portal.
    |
    */
    'use_customers' => false,

    /*
    |--------------------------------------------------------------------------
    | Customer Model (Optional)
    |--------------------------------------------------------------------------
    |
    | If your application has a separate Customer model (e.g., for a customer portal),
    | specify it here. Customers will be excluded from admin navigation.
    | Only used when 'use_customers' is set to true.
    |
    */
    'customer_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Column Preference Model (Optional)
    |--------------------------------------------------------------------------
    |
    | If you want to use the built-in column preference feature, specify the
    | model class here. The model should implement the ColumnPreferenceRepository
    | interface or use the provided UserColumnPreference model.
    |
    */
    'column_preference_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Menu Models (Optional)
    |--------------------------------------------------------------------------
    |
    | If you want to use the database-driven menu system, specify the model
    | classes here. These models will be used by MenuBuilder to build the
    | navigation menu from the database.
    |
    */
    'menu_group_model' => \App\Models\MenuGroup::class,
    'menu_item_model' => \App\Models\MenuItem::class,

    /*
    |--------------------------------------------------------------------------
    | Menu Permission Behavior
    |--------------------------------------------------------------------------
    |
    | Controls how menu items without a permission_name are handled:
    | - true: Items without permission_name are shown to all users (default)
    | - false: Items without permission_name are hidden from all users
    |
    */
    'menu_show_items_without_permission' => true,

    /*
    |--------------------------------------------------------------------------
    | Resource Paths
    |--------------------------------------------------------------------------
    |
    | Array of paths where InertiaResource classes are located.
    | These paths will be scanned for resource files.
    |
    */
    'resource_paths' => [
        app_path('Support/Inertia/Resources'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Resource Path (Optional)
    |--------------------------------------------------------------------------
    |
    | If you want to include Filament resources in navigation, specify the path.
    | Set to null to disable Filament resource scanning.
    |
    */
    'filament_resource_path' => app_path('Filament/Resources'),

    /*
    |--------------------------------------------------------------------------
    | Filament Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace prefix for Filament resources.
    |
    */
    'filament_namespace' => 'App\\Filament\\Resources',

    /*
    |--------------------------------------------------------------------------
    | Default Page Templates
    |--------------------------------------------------------------------------
    |
    | Default Vue page component paths for resource pages.
    | These can be overridden in individual resources.
    |
    */
    'default_pages' => [
        'index' => 'Resources/Index',
        'create' => 'Resources/Create',
        'edit' => 'Resources/Edit',
        'show' => 'Resources/Show',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used for Inertia resource routes (e.g., 'vue' for 'vue.customers.index').
    |
    */
    'route_prefix' => 'vue',
];

