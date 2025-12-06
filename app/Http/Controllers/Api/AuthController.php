<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Program;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => 'required|in:student,faculty',
            'phone' => 'required|string|max:20',
        ];

        // Add user type specific validation rules
        if ($request->user_type === 'student') {
            $rules = array_merge($rules, [
                'student_id' => 'required|string|max:50|unique:user_details,student_id',
                'program_id' => 'required|exists:programs,id',
                'semester' => 'required|string|max:50',
                'intake' => 'required|integer|min:20|max:90',
                'section' => 'required|integer|min:1|max:50',
                'cgpa' => 'nullable|numeric|min:0|max:4.00',
            ]);
        } else {
            $rules = array_merge($rules, [
                'faculty_code' => 'required|string|max:50|unique:user_details,faculty_code',
                'department' => 'required|string|max:255',
                'designation' => 'required|string|max:100',
            ]);
        }

        // Validate request
        $validated = $request->validate($rules);

        // Use transaction to ensure data consistency
        try {
            DB::beginTransaction();

            // Determine user status based on type
            $status = 'active'; // Default for students
            $isVerified = true; // Default for students
            
            if ($validated['user_type'] === 'faculty') {
                $status = 'blocked'; // Block faculty until admin verification
                $isVerified = false;
            }

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $status,
                'email_verified_at' => $isVerified ? now() : null,
            ]);

            // Prepare user details data
            $userDetailsData = [
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'is_verified' => $isVerified,
            ];

            // Add user type specific data
            if ($validated['user_type'] === 'student') {
                $userDetailsData = array_merge($userDetailsData, [
                    'student_id' => $validated['student_id'],
                    'program_id' => $validated['program_id'],
                    'semester' => $validated['semester'],
                    'intake' => $validated['intake'],
                    'section' => $validated['section'],
                    'cgpa' => $validated['cgpa'] ?? null,
                ]);
            } else {
                $userDetailsData = array_merge($userDetailsData, [
                    'faculty_code' => $validated['faculty_code'],
                    'department' => $validated['department'],
                    'designation' => $validated['designation'],
                ]);
            }

            // Create user details
            UserDetail::create($userDetailsData);

            // Assign role based on user type
            if ($validated['user_type'] === 'student') {
                $user->assignRole('student');
            } else {
                $user->assignRole('faculty');
            }

            DB::commit();

            event(new Registered($user));

            // Load relationships
            $user->load(['userDetail.program', 'roles', 'permissions']);

            // Only create token if user is active (students)
            $token = null;
            if ($user->status === 'active') {
                $token = $user->createToken('auth_token')->plainTextToken;
            }

            $authData = (object) [
                'access_token' => $token,
                'token_type' => $token ? 'Bearer' : null,
                'user_type' => $validated['user_type'],
                'user' => $user,
            ];

            // Prepare response message based on user type
            $message = $user->status === 'active' 
                ? 'Account created successfully! Welcome to the platform.'
                : 'Your faculty account has been created and is pending admin verification. You will be notified once your account is approved.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => new AuthResource($authData)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            Log::error('API Registration failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::with(['userDetail.program', 'roles', 'permissions'])
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Check if user is blocked
        if ($user->status === 'blocked') {
            // Check if it's a faculty member
            if ($user->hasRole('faculty')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your faculty account is pending admin verification. Please wait for approval.',
                ], 403);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been blocked. Please contact administrator.',
                ], 403);
            }
        }

        // Check if faculty is verified
        // if ($user->hasRole('faculty') && !$user->userDetail?->is_verified) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Your faculty account verification is still pending. Please wait for admin approval.',
        //     ], 403);
        // }

        $token = $user->createToken('auth_token')->plainTextToken;

        $authData = (object) [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user_type' => $user->user_type,
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
     * Get available programs for registration (for mobile app)
     */
    public function getPrograms(): JsonResponse
    {
        $programs = Program::where('is_active', true)
            ->select(['id', 'name', 'code', 'description'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $programs
        ]);
    }

    /**
     * Get semester options (for mobile app)
     */
    public function getSemesterOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Http\Class\Data::getSemesterOptions()
        ]);
    }

    /**
     * Get department options (for mobile app)
     */
    public function getDepartmentOptions(): JsonResponse
    {

        return response()->json([
            'success' => true,
            'data' => \App\Http\Class\Data::getDepartmentOptions()
        ]);
    }

    /**
     * Get designation options (for mobile app)
     */
    public function getDesignationOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Http\Class\Data::getDesignationOptions()
        ]);
    }
}