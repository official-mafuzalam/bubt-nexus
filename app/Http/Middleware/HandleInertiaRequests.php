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

            // Authenticated User
            'auth' => [
                'user' => $request->user(),
            ],

            // Sidebar State
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // Flash Messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info'    => fn () => $request->session()->get('info'),
            ],

            // Global Settings
            'settings' => fn () => $this->getAllSettings(),
        ];
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
