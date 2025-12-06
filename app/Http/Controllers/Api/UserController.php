<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::with(['userDetail', 'roles', 'permissions'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => [
                'users' => UserResource::collection($users),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load('userDetail', 'roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => [
                'user' => new UserResource($user)
            ]
        ]);
    }

    /**
     * Get current authenticated user with full details
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('userDetail.program', 'roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => [
                'user' => new UserResource($user)
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        // Update basic user info
        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('email') && $request->email !== $user->email) {
            $updateData['email'] = $request->email;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Handle profile picture upload
        $userDetailsData = $request->except(['name', 'email', 'profile_picture', '_method']);

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->userDetail && $user->userDetail->profile_picture) {
                Storage::disk('public')->delete($user->userDetail->profile_picture);
            }

            $userDetailsData['profile_picture'] = $request->file('profile_picture')
                ->store('profile-pictures', 'public');
        }

        // Update or create user details
        if ($user->userDetail) {
            $user->userDetail->update(array_filter($userDetailsData));
        } elseif (!empty(array_filter($userDetailsData))) {
            $userDetailsData['user_id'] = $user->id;
            UserDetail::create($userDetailsData);
        }

        // Reload relationships
        $user->load(['userDetail.program', 'roles', 'permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => new UserResource($user)
            ]
        ]);
    }
}