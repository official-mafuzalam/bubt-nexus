<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ProfileUpdateRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Add this import
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create user details if any provided
        $userDetailsData = $request->only([
            'student_id',
            'faculty_id',
            'program',
            'semester',
            'department',
            'phone'
        ]);
        $userDetailsData['user_id'] = $user->id;

        // Only create user details if at least one field is provided
        if (!empty(array_filter($userDetailsData))) {
            UserDetail::create($userDetailsData);
        }

        // Load relationships
        $user->load('userDetail', 'roles', 'permissions');

        $token = $user->createToken('auth_token')->plainTextToken;

        $authData = (object) [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => new AuthResource($authData)
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::with(['userDetail', 'roles', 'permissions'])
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $authData = (object) [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => new AuthResource($authData)
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load('userDetail', 'roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => 'User data retrieved successfully',
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
        $user->update($request->only(['name', 'email']));

        // Handle profile picture upload
        $userDetailsData = $request->except(['name', 'email', 'profile_picture']);

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
            $user->userDetail->update($userDetailsData);
        } else {
            $userDetailsData['user_id'] = $user->id;
            UserDetail::create($userDetailsData);
        }

        // Reload relationships
        $user->load('userDetail', 'roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => new UserResource($user)
            ]
        ]);
    }
}