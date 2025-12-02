<?php

namespace InertiaResource\Inertia;

use Illuminate\Support\Facades\Route;

class MenuBuilder
{
    /**
     * Build menu structure from database models.
     * 
     * This method expects MenuGroup and MenuItem models to exist in the App\Models namespace.
     * It will return an array structure compatible with the Vue sidebar components.
     * 
     * Usage example:
     * 
     * In your AppServiceProvider or middleware, share the menu with Inertia:
     * 
     * use InertiaResource\Inertia\MenuBuilder;
     * 
     * Inertia::share('menu', MenuBuilder::build());
     *
     * @return array
     */
    public static function build(): array
    {
        $menuGroupModel = config('inertia-resource.menu_group_model', \App\Models\MenuGroup::class);
        $menuItemModel = config('inertia-resource.menu_item_model', \App\Models\MenuItem::class);

        // Check if models exist
        if (!class_exists($menuGroupModel) || !class_exists($menuItemModel)) {
            return [];
        }

        $user = auth()->user();
        if (!$user) {
            return [];
        }

        // Get user permissions
        $userPermissions = [];
        if (method_exists($user, 'getAllPermissions')) {
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        } elseif (method_exists($user, 'permissions')) {
            $userPermissions = $user->permissions->pluck('name')->toArray();
        }

        // Load active menu groups with their items (recursively load all nested children)
        $menuGroups = $menuGroupModel::with([
            'activeMenuItems' => function ($query) {
                $query->whereNull('parent_id')->orderBy('sort_order');
            },
            'activeMenuItems.activeChildren' => function ($query) {
                $query->orderBy('sort_order');
            },
            'activeMenuItems.activeChildren.activeChildren' => function ($query) {
                $query->orderBy('sort_order');
            }
        ])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        $menu = [];

        $showItemsWithoutPermission = config('inertia-resource.menu_show_items_without_permission', true);

        foreach ($menuGroups as $group) {
            $items = self::buildMenuItems($group->activeMenuItems, $userPermissions, $menuItemModel, $showItemsWithoutPermission);

            // Only include groups that have items
            if (!empty($items)) {
                $menu[] = [
                    'key' => $group->key,
                    'label' => $group->label,
                    'icon' => $group->icon,
                    'items' => $items,
                ];
            }
        }

        return $menu;
    }

    /**
     * Recursively build menu items with permission checking.
     *
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param array $userPermissions
     * @param string $menuItemModel
     * @param bool $showItemsWithoutPermission
     * @return array
     */
    protected static function buildMenuItems($items, array $userPermissions, string $menuItemModel, bool $showItemsWithoutPermission = true): array
    {
        $result = [];

        foreach ($items as $item) {
            // Check permission if set
            if ($item->permission_name) {
                // Item has permission - check if user has it
                if (!in_array($item->permission_name, $userPermissions, true)) {
                    continue;
                }
            } else {
                // Item has no permission - check config setting
                if (!$showItemsWithoutPermission) {
                    continue;
                }
            }

            // Build URL
            $url = self::buildUrl($item);

            // Build menu item structure
            $menuItem = [
                'key' => $item->key,
                'label' => $item->label,
                'icon' => $item->icon,
                'url' => $url,
            ];

            // Add children if they exist (check both loaded relation and lazy load if needed)
            $children = null;
            if ($item->relationLoaded('activeChildren')) {
                $children = $item->activeChildren;
            } else {
                // Lazy load if not already loaded
                $children = $item->activeChildren;
            }
            
            if ($children && $children->isNotEmpty()) {
                $menuItem['children'] = self::buildMenuItems($children, $userPermissions, $menuItemModel, $showItemsWithoutPermission);
            }

            $result[] = $menuItem;
        }

        return $result;
    }

    /**
     * Build URL from menu item (route or direct URL).
     *
     * @param \App\Models\MenuItem $item
     * @return string
     */
    protected static function buildUrl($item): string
    {
        if ($item->route) {
            // Check if route exists
            if (Route::has($item->route)) {
                return route($item->route);
            }
        }

        return $item->url ?? '#';
    }
}

