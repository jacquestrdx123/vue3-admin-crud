<?php

namespace InertiaResource\Inertia;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Navigation
{
    public static function get(): array
    {
        $userModel = config('inertia-resource.user_model');
        $memberModel = config('inertia-resource.member_model');

        $user = auth()->user();
        if (! $user) {
            return [];
        }

        // Members don't have admin navigation - only User models do
        if ($memberModel && $user instanceof $memberModel) {
            return [];
        }

        // Load user permissions once to avoid repeated queries
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        $resourceFiles = [];
        foreach (config('inertia-resource.resource_paths', []) as $path) {
            if (is_dir($path)) {
                $resourceFiles = array_merge($resourceFiles, glob($path.'/*Resource.php'));
            }
        }

        // Optionally scan Filament resources
        $filamentPath = config('inertia-resource.filament_resource_path');
        if ($filamentPath && is_dir($filamentPath)) {
            $resourceFiles = array_merge($resourceFiles, glob($filamentPath.'/*Resource.php'));
        }

        $resources = [];

        foreach ($resourceFiles as $file) {
            // Determine namespace based on file path
            $basePath = app_path();
            $relativePath = Str::after($file, $basePath.DIRECTORY_SEPARATOR);
            $namespacePart = str_replace('/', '\\', Str::beforeLast($relativePath, '.php'));
            $className = 'App\\'.Str::beforeLast($namespacePart, '\\').'\\'.basename($file, '.php');

            // Adjust namespace for package resources if needed
            if (Str::startsWith($className, 'App\\Support\\Inertia\\Resources\\')) {
                $className = 'App\\Support\\Inertia\\Resources\\'.basename($file, '.php');
            } elseif (Str::startsWith($className, 'App\\Filament\\Resources\\')) {
                $filamentNamespace = config('inertia-resource.filament_namespace', 'App\\Filament\\Resources');
                $className = $filamentNamespace.'\\'.basename($file, '.php');
            }

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new \ReflectionClass($className);

                $navigationIcon = $reflection->hasProperty('navigationIcon')
                    ? $reflection->getProperty('navigationIcon')->getDefaultValue()
                    : 'heroicon-o-cube';

                $navigationGroup = $reflection->hasProperty('navigationGroup')
                    ? $reflection->getProperty('navigationGroup')->getDefaultValue()
                    : 'Other';

                $navigationSort = $reflection->hasProperty('navigationSort')
                    ? $reflection->getProperty('navigationSort')->getDefaultValue()
                    : 999;

                $navigationLabel = $reflection->hasProperty('navigationLabel')
                    ? $reflection->getProperty('navigationLabel')->getDefaultValue()
                    : null;

                $customSlug = $reflection->hasProperty('slug')
                    ? $reflection->getProperty('slug')->getDefaultValue()
                    : null;

                $customTitle = $reflection->hasProperty('title')
                    ? $reflection->getProperty('title')->getDefaultValue()
                    : null;

                $model = $reflection->hasProperty('model')
                    ? $reflection->getProperty('model')->getDefaultValue()
                    : null;

                if (! $model) {
                    continue;
                }

                $modelName = class_basename($model);
                $slug = $customSlug ?? \Illuminate\Support\Str::kebab(\Illuminate\Support\Str::plural($modelName));
                $label = $navigationLabel ?? $customTitle ?? \Illuminate\Support\Str::plural($modelName);

                $routePrefix = config('inertia-resource.route_prefix', 'vue');
                if (! Route::has($routePrefix.'.'.$slug.'.index')) {
                    continue;
                }

                $snakeCase = \Illuminate\Support\Str::snake($modelName);
                $permissionUnderscore = 'view_any_'.$snakeCase;
                $permissionFilament = 'view_any_'.str_replace('_', '::', $snakeCase);

                $hasPermission = in_array($permissionUnderscore, $userPermissions, true)
                    || in_array($permissionFilament, $userPermissions, true);

                if (! $hasPermission) {
                    continue;
                }

                $resources[] = [
                    'name' => $slug,
                    'label' => $label,
                    'url' => route($routePrefix.'.'.$slug.'.index'),
                    'icon' => $navigationIcon,
                    'group' => $navigationGroup ?? 'Other',
                    'sort' => $navigationSort ?? 999,
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        $uniqueResources = collect($resources)
            ->unique('name')
            ->values();

        $grouped = $uniqueResources
            ->groupBy('group')
            ->map(function ($items, $groupName) {
                return [
                    'label' => $groupName,
                    'items' => $items->sortBy('sort')->values()->all(),
                ];
            })
            ->sortBy(function ($group) {
                return $group['items'][0]['sort'] ?? 999;
            })
            ->values()
            ->all();

        return $grouped;
    }
}

