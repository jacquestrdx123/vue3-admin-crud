<?php

namespace InertiaResource\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface ColumnPreferenceRepository
{
    /**
     * Get column preferences for a user and resource
     *
     * @param  Authenticatable  $user
     * @param  string  $resourceSlug
     * @return array|null Returns null if no preferences exist, or array with 'order' and 'hidden' keys
     */
    public function getPreferencesForResource(Authenticatable $user, string $resourceSlug): ?array;

    /**
     * Save column preferences for a user and resource
     *
     * @param  Authenticatable  $user
     * @param  string  $resourceSlug
     * @param  array  $preferences Array with 'order' and 'hidden' keys
     * @return void
     */
    public function savePreferencesForResource(Authenticatable $user, string $resourceSlug, array $preferences): void;
}

