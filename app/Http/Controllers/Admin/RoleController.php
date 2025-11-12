<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->paginate(10);

        return Inertia::render('admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode(' ', $permission->name)[0] ?? 'general';
        });

        return Inertia::render('admin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'min:3', 'unique:roles,name'],
            'permissions' => ['sometimes', 'array'],
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create(['name' => $validated['name']]);

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }
        });

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        // Load role with permissions and users count
        $role->load([
            'permissions' => function ($query) {
                $query->orderBy('name');
            }
        ]);

        $role->loadCount('users');

        // Get all permissions for the assign permission dropdown
        $allPermissions = Permission::orderBy('name')->get();

        return Inertia::render('admin/Roles/Show', [
            'role' => $role,
            'allPermissions' => $allPermissions,
        ]);
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode(' ', $permission->name)[0] ?? 'general';
        });

        return Inertia::render('admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'min:3', 'unique:roles,name,' . $role->id],
            'permissions' => ['sometimes', 'array'],
        ]);

        DB::transaction(function () use ($role, $validated) {
            $role->update(['name' => $validated['name']]);

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }
        });

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role that has users assigned.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function givePermission(Request $request, Role $role)
    {
        $request->validate([
            'permission' => ['required', 'exists:permissions,name']
        ]);

        if ($role->hasPermissionTo($request->permission)) {
            return back()->with('info', 'Permission already exists.');
        }

        $role->givePermissionTo($request->permission);

        return back()->with('success', 'Permission added successfully.');
    }

    public function revokePermission(Role $role, Permission $permission)
    {
        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            return back()->with('success', 'Permission revoked successfully.');
        }

        return back()->with('info', 'Permission does not exist.');
    }
    
}