<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register', [
            'programs' => Program::where('is_active', true)->get(['id', 'name', 'code']),
            'semesterOptions' => \App\Http\Class\Data::getSemesterOptions(),
            'departments' => \App\Http\Class\Data::getDepartmentOptions(),
            'designations' => \App\Http\Class\Data::getDesignationOptions(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => 'required|in:student,faculty',
            'user_details.phone' => 'required|string|max:20',
        ];

        // Add user type specific validation rules
        if ($request->user_type === 'student') {
            $rules = array_merge($rules, [
                'user_details.student_id' => 'required|string|max:50|unique:user_details,student_id',
                'user_details.program_id' => 'required|exists:programs,id',
                'user_details.semester' => 'required|string|max:50',
                'user_details.intake' => 'required|integer|min:20|max:90',
                'user_details.section' => 'required|integer|min:1|max:50',
                'user_details.cgpa' => 'nullable|numeric|min:0|max:4.00',
            ]);
        } else {
            $rules = array_merge($rules, [
                'user_details.faculty_code' => 'required|string|max:50|unique:user_details,faculty_code',
                'user_details.department' => 'required|string|max:255',
                'user_details.designation' => 'required|string|max:100',
            ]);
        }

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
                'phone' => $validated['user_details']['phone'],
                'is_verified' => $isVerified, // Add verification status to user_details
            ];

            // Add user type specific data
            if ($validated['user_type'] === 'student') {
                $userDetailsData = array_merge($userDetailsData, [
                    'student_id' => $validated['user_details']['student_id'],
                    'program_id' => $validated['user_details']['program_id'],
                    'semester' => $validated['user_details']['semester'],
                    'intake' => $validated['user_details']['intake'],
                    'section' => $validated['user_details']['section'],
                    'cgpa' => $validated['user_details']['cgpa'] ?? null,
                ]);
            } else {
                $userDetailsData = array_merge($userDetailsData, [
                    'faculty_code' => $validated['user_details']['faculty_code'],
                    'department' => $validated['user_details']['department'],
                    'designation' => $validated['user_details']['designation'],
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

            // Only login if user is not blocked (students)
            if ($user->status === 'active') {
                Auth::login($user);
                $request->session()->regenerate();
                
                return redirect()->route('dashboard')->with([
                    'success' => 'Account created successfully! Welcome to the platform.',
                ]);
            } else {
                // For faculty (blocked), show message and redirect to login
                return redirect()->route('login')->with([
                    'info' => 'Your faculty account has been created and is pending admin verification. You will be notified once your account is approved.',
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            Log::error('Registration failed: ' . $e->getMessage());
            
            return back()->with([
                'error' => 'Registration failed. Please try again later.'
            ])->withInput();
        }
    }
}