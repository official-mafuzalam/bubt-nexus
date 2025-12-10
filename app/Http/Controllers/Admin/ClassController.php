<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\ClassEnrollment;
use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('faculty')) {
            $classes = ClassModel::withCount(['enrollments', 'assignments'])
                ->where('faculty_id', $user->id)
                ->where('is_active', true)
                ->latest()
                ->paginate(10);
        } elseif ($user->hasRole('student')) {
            $classIds = ClassEnrollment::where('student_id', $user->id)
                ->where('status', 'active')
                ->pluck('class_id');

            $classes = ClassModel::withCount(['enrollments', 'assignments'])
                ->whereIn('id', $classIds)
                ->where('is_active', true)
                ->latest()
                ->paginate(10);
        } else {
            // Return empty paginated result instead of collect()
            $classes = ClassModel::where('id', 0)->paginate(10); // Empty pagination
        }

        return Inertia::render('admin/Classes/Index', [
            'classes' => $classes,
            'userType' => $user->hasRole('faculty') ? 'faculty' : 'student',
        ]);
    }

    public function create()
    {
        $programs = Program::where('is_active', true)->get();
        return Inertia::render('admin/Classes/Create', [
            'programs' => $programs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject_code' => 'required|string|max:50',
            'program_id' => 'required|integer|exists:programs,id',
            'intake' => 'required|integer',
            'section' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $class = ClassModel::create([
            'name' => $request->name,
            'code' => strtoupper(substr(md5(uniqid()), 0, 6)),
            'description' => $request->description,
            'faculty_id' => Auth::id(),
            'subject_code' => $request->subject_code,
            'program_id' => $request->program_id,
            'intake' => $request->intake,
            'section' => $request->section,
            'enrollment_code' => ClassModel::generateEnrollmentCode(),
        ]);

        return redirect()->route('admin.classes.show', $class->id)
            ->with('success', 'Class created successfully!');
    }

    public function show($id)
    {
        $class = ClassModel::with([
            'faculty',
            'enrollments.student.userDetail',
            'assignments' => function ($query) {
                $query->withCount('submissions');
            }
        ])->findOrFail($id);

        $user = Auth::user();
        $isEnrolled = ClassEnrollment::where('class_id', $id)
            ->where('student_id', $user->id)
            ->where('status', 'active')
            ->exists();

        return Inertia::render('admin/Classes/Show', [
            'classData' => $class,
            'isEnrolled' => $isEnrolled,
            'isFaculty' => $class->faculty_id === $user->id,
            'enrollmentCount' => $class->enrollments()->where('status', 'active')->count(),
        ]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'enrollment_code' => 'required|string|exists:classes,enrollment_code',
        ]);

        $class = ClassModel::where('enrollment_code', $request->enrollment_code)
            ->where('is_active', true)
            ->firstOrFail();

        // Check if already enrolled
        $existing = ClassEnrollment::where('class_id', $class->id)
            ->where('student_id', Auth::id())
            ->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return back()->with('error', 'You are already enrolled in this class.');
            } else {
                $existing->update(['status' => 'active']);
                return back()->with('success', 'Successfully rejoined the class!');
            }
        }

        ClassEnrollment::create([
            'class_id' => $class->id,
            'student_id' => Auth::id(),
        ]);

        return back()->with('success', 'Successfully joined the class!');
    }

    public function removeStudent($classId, $studentId)
    {
        $class = ClassModel::findOrFail($classId);

        // Only faculty can remove students
        if ($class->faculty_id !== Auth::id()) {
            abort(403);
        }

        ClassEnrollment::where('class_id', $classId)
            ->where('student_id', $studentId)
            ->update(['status' => 'dropped']);

        return back()->with('success', 'Student removed from class.');
    }
}