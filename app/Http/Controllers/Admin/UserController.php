<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all()->map(function ($user) {
            $lastSeenDetailed = $user->getLastSeenDetailed();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'is_online' => $user->isOnline(),
                'online_status' => $user->getOnlineStatus(),
                'last_seen_at' => $user->last_seen_at,
                'last_seen_text' => $user->getLastSeenText(),
                'last_seen_detailed' => $lastSeenDetailed,
                'user_type' => $user->userDetail ? $user->userDetail->user_type : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return Inertia::render('admin/Users/Index', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Load with nested relationships
        $user->load([
            'roles',
            'permissions',
            'userDetail' => function ($query) {
                $query->with('program'); // Explicitly load program within userDetail
            }
        ]);

        $allRoles = Role::orderBy('name')->get();
        $allPermissions = Permission::orderBy('name')->get();

        // Build user data
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'last_seen_at' => $user->last_seen_at?->toISOString(),
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
            'roles' => $user->roles,
            'permissions' => $user->permissions,
        ];

        // Build user detail data
        if ($user->userDetail) {
            $userDetailData = [
                'phone' => $user->userDetail->phone,
                'student_id' => $user->userDetail->student_id,
                'faculty_code' => $user->userDetail->faculty_code,
                'semester' => $user->userDetail->semester,
                'intake' => $user->userDetail->intake,
                'section' => $user->userDetail->section,
                'cgpa' => $user->userDetail->cgpa,
                'department' => $user->userDetail->department,
                'designation' => $user->userDetail->designation,
                'profile_picture' => $user->userDetail->profile_picture,
                'user_type' => $user->userDetail->user_type,
                'program' => null, // Initialize as null
            ];

            // Check if program is loaded and exists
            if ($user->userDetail->program && $user->userDetail->program->exists) {
                $userDetailData['program'] = [
                    'id' => $user->userDetail->program->id,
                    'name' => $user->userDetail->program->name,
                    'code' => $user->userDetail->program->code,
                ];
            }

            $userData['user_detail'] = $userDetailData;
        } else {
            $userData['user_detail'] = null;
        }

        return Inertia::render('admin/Users/Show', [
            'user' => $userData,
            'allRoles' => $allRoles,
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }
        return Inertia::render('admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Update user status (block/unblock)
     */
    /**
     * Update user status (block/unblock)
     */
    public function updateStatus(Request $request, User $user)
    {
        // Prevent users from blocking themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.edit', $user)
                ->with('error', 'You cannot block your own account.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:active,blocked',
        ]);

        $oldStatus = $user->status;
        $user->update([
            'status' => $validated['status']
        ]);

        $action = $validated['status'] === 'blocked' ? 'blocked' : 'unblocked';

        // ✅ If user was blocked, invalidate their sessions
        if ($validated['status'] === 'blocked' && $oldStatus === 'active') {
            $this->invalidateUserSessions($user);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User {$action} successfully!");
    }

    /**
     * Invalidate all active sessions for a user
     */
    protected function invalidateUserSessions(User $user)
    {
        // You can implement session invalidation logic here
        // This is a simple approach - in production you might want to use database sessions

        Log::info("User {$user->id} ({$user->email}) was blocked. Sessions invalidated.");

        // If you're using database sessions, you can delete them:
        // \DB::table('sessions')->where('user_id', $user->id)->delete();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userName} deleted successfully!");
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'exists:roles,name']
        ]);

        if ($user->hasRole($request->role)) {
            return back()->with('info', 'User already has this role.');
        }

        $user->assignRole($request->role);

        return back()->with('success', 'Role assigned successfully.');
    }

    public function removeRole(User $user, Role $role)
    {
        if ($user->hasRole($role)) {
            $user->removeRole($role);
            return back()->with('success', 'Role removed successfully.');
        }

        return back()->with('info', 'User does not have this role.');
    }

    public function givePermission(Request $request, User $user)
    {
        $request->validate([
            'permission' => ['required', 'exists:permissions,name']
        ]);

        if ($user->hasPermissionTo($request->permission)) {
            return back()->with('info', 'User already has this permission.');
        }

        $user->givePermissionTo($request->permission);

        return back()->with('success', 'Permission added successfully.');
    }

    public function revokePermission(User $user, Permission $permission)
    {
        if ($user->hasPermissionTo($permission)) {
            $user->revokePermissionTo($permission);
            return back()->with('success', 'Permission revoked successfully.');
        }

        return back()->with('info', 'User does not have this permission.');
    }

}