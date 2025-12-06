<?php
namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Clear the user's auth data cache when user is updated
        Cache::forget("user_{$user->id}_auth_data");
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Cache::forget("user_{$user->id}_auth_data");
    }
}