<?php

namespace App\Http\Controllers\Settings;

use App\Http\Class\Data;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */

    public function edit(Request $request): Response
    {
        $user = $request->user()->load('userDetail.program');

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'user' => $user,
            'isFaculty' => $user->hasRole('faculty'),
            'isStudent' => $user->hasRole('student'),

            // pass select options
            'semesterOptions' => Data::getSemesterOptions(),
            'departmentOptions' => Data::getDepartmentOptions(),
            'designationOptions' => Data::getDesignationOptions(),
        ]);
    }


    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update related user_detail fields
        $userDetailData = $request->only([
            'phone',
            'program_id',
            'semester',
            'intake',
            'section',
            'student_id',
            'cgpa',
            'department',
            'faculty_code',
            'designation',
        ]);

        // Make sure userDetail exists
        $user->userDetail()->updateOrCreate([], $userDetailData);

        return to_route('profile.edit')->with('status', 'Profile updated successfully!');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
