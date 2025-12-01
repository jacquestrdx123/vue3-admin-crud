<?php

namespace InertiaResource\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Column Preference Model
 *
 * This model stores user preferences for table column visibility and ordering.
 * This is an optional feature - you can use your own model by implementing
 * the ColumnPreferenceRepository interface.
 */
class UserColumnPreference extends Model
{
    protected $fillable = [
        'user_id',
        'resource_slug',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        $userModel = config('inertia-resource.user_model', \App\Models\User::class);

        return $this->belongsTo($userModel);
    }

    public static function getPreferencesForResource($user, string $resourceSlug): ?array
    {
        if (! $user || ! method_exists($user, 'getKey')) {
            return null;
        }

        $preference = static::where('user_id', $user->getKey())
            ->where('resource_slug', $resourceSlug)
            ->first();

        return $preference?->preferences;
    }

    public static function savePreferencesForResource($user, string $resourceSlug, array $preferences): self
    {
        if (! $user || ! method_exists($user, 'getKey')) {
            throw new \InvalidArgumentException('User must have a getKey() method');
        }

        return static::updateOrCreate(
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

