<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Shared props for Inertia.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // App Name
            'name' => config('app.name'),

            // Authenticated User with roles and permissions
            'auth' => [
                'user' => $this->getUserData($request),
            ],

            // Sidebar State
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // Flash Messages
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],

            // Global Settings
            'settings' => fn() => $this->getAllSettings(),
        ];
    }

    /**
     * Get user data with roles and permissions.
     */
    protected function getUserData(Request $request): ?array
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        // Cache user roles and permissions for better performance
        $cacheKey = "user_{$user->id}_auth_data";
        $cacheTime = 300; // 5 minutes

        return Cache::remember($cacheKey, $cacheTime, function () use ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray(),
                'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                // Add any other user fields you need
                // 'avatar' => $user->avatar,
                // 'username' => $user->username,
                // 'created_at' => $user->created_at->toDateTimeString(),
            ];
        });
    }

    /**
     * Get all settings with caching.
     */
    protected function getAllSettings(): array
    {
        return Cache::remember('app_settings', 3600, function () {
            return Setting::all()
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}