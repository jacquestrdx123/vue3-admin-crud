<?php

namespace InertiaResource\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InertiaResource\Contracts\ColumnPreferenceRepository;

class UserColumnPreferenceController
{
    public function show(string $resourceSlug): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (!app()->bound(ColumnPreferenceRepository::class)) {
            return response()->json(['error' => 'Column preferences not configured'], 404);
        }

        /** @var ColumnPreferenceRepository $repository */
        $repository = app(ColumnPreferenceRepository::class);
        $preferences = $repository->getPreferencesForResource($user, $resourceSlug);

        return response()->json([
            'preferences' => $preferences,
        ]);
    }

    public function store(Request $request, string $resourceSlug): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (!app()->bound(ColumnPreferenceRepository::class)) {
            return response()->json(['error' => 'Column preferences not configured'], 404);
        }

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'hidden' => ['required', 'array'],
        ]);

        /** @var ColumnPreferenceRepository $repository */
        $repository = app(ColumnPreferenceRepository::class);
        $repository->savePreferencesForResource($user, $resourceSlug, [
            'order' => $validated['order'],
            'hidden' => $validated['hidden'],
        ]);

        $preferences = $repository->getPreferencesForResource($user, $resourceSlug);

        return response()->json([
            'preferences' => $preferences,
        ]);
    }

    public function destroy(string $resourceSlug): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (!app()->bound(ColumnPreferenceRepository::class)) {
            return response()->json(['error' => 'Column preferences not configured'], 404);
        }

        $preferenceModel = config('inertia-resource.column_preference_model');
        
        if (!$preferenceModel || !class_exists($preferenceModel)) {
            return response()->json(['error' => 'Column preferences not configured'], 404);
        }

        $preferenceModel::where('user_id', $user->getKey())
            ->where('resource_slug', $resourceSlug)
            ->delete();

        return response()->json([
            'message' => 'Preferences reset successfully',
        ]);
    }
}

