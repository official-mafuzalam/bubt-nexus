<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('roles')
            ->withCount(['roles', 'users'])
            ->paginate(10);

        return Inertia::render('admin/Permission/Index', [
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/Permission/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'unique:permissions,name']
        ]);

        Permission::create($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        // Load permission with roles and users count
        $permission->load([
            'roles' => function ($query) {
                $query->orderBy('name');
            }
        ]);

        $permission->loadCount('users');

        // Get all roles for the assign role dropdown
        $allRoles = Role::orderBy('name')->get();

        return Inertia::render('admin/Permission/Show', [
            'permission' => $permission,
            'allRoles' => $allRoles,
        ]);
    }

    public function edit($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        $roles = Role::all();

        return Inertia::render('admin/Permission/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'roles' => $permission->roles->pluck('name'),
            ],
            'allRoles' => $roles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'unique:permissions,name,' . $permission->id]
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Check if permission has roles assigned
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Cannot delete permission that has roles assigned.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    public function assignRole(Request $request, Permission $permission)
    {
        $request->validate([
            'role' => ['required', 'exists:roles,name']
        ]);

        $role = Role::where('name', $request->role)->first();

        if ($permission->hasRole($role)) {
            return back()->with('info', 'Role already has this permission.');
        }

        $permission->assignRole($role);

        return back()->with('success', 'Role assigned successfully.');
    }

    public function removeRole(Permission $permission, Role $role)
    {
        if ($permission->hasRole($role)) {
            $permission->removeRole($role);
            return back()->with('success', 'Role removed successfully.');
        }

        return back()->with('info', 'Role does not have this permission.');
    }
}