<?php

namespace InertiaResource\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use InertiaResource\Contracts\ColumnPreferenceRepository;

class UserColumnPreferenceRepository implements ColumnPreferenceRepository
{
    protected string $modelClass;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function getPreferencesForResource(Authenticatable $user, string $resourceSlug): ?array
    {
        if (! $user || ! method_exists($user, 'getKey')) {
            return null;
        }

        $preference = $this->modelClass::where('user_id', $user->getKey())
            ->where('resource_slug', $resourceSlug)
            ->first();

        return $preference?->preferences ?? null;
    }

    public function savePreferencesForResource(Authenticatable $user, string $resourceSlug, array $preferences): void
    {
        if (! $user || ! method_exists($user, 'getKey')) {
            return;
        }

        $this->modelClass::updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'resource_slug' => $resourceSlug,
            ],
            [
                'preferences' => $preferences,
            ]
        );
    }
}

