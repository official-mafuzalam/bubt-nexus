<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index($classId)
    {
        $class = ClassModel::findOrFail($classId);
        $user = Auth::user();

        // Check if user has access to this class
        $this->checkClassAccess($class, $user);

        $assignments = Assignment::where('class_id', $classId)
            ->withCount('submissions')
            ->latest()
            ->paginate(10);

        // Convert everything to arrays explicitly
        $classArray = [
            'id' => (int) $class->id,
            'name' => (string) $class->name,
            'subject_code' => (string) $class->subject_code,
            'semester' => (int) $class->semester,
            'section' => (string) $class->section,
        ];

        // Convert assignments to simple array
        $assignmentsArray = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'total_marks' => $assignment->total_marks,
                'deadline' => $assignment->deadline,
                'status' => $assignment->status,
                'submissions_count' => $assignment->submissions_count,
            ];
        })->toArray();

        // Use Inertia::render with explicit arrays
        return Inertia::render('admin/Assignments/Index', [
            'class' => $classArray,
            'assignments' => [
                'data' => $assignmentsArray,
                'links' => $assignments->linkCollection()->toArray(),
            ],
            'isFaculty' => $class->faculty_id === $user->id,
        ]);
    }

    public function create($classId)
    {
        $class = ClassModel::findOrFail($classId);

        return Inertia::render('admin/Assignments/Create', [
            'classData' => $class->only(['id', 'name', 'subject_code', 'semester', 'section']),
        ]);
    }

    public function store(Request $request, $classId)
    {
        $class = ClassModel::findOrFail($classId);

        if ($class->faculty_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'nullable|string',
            'total_marks' => 'required|numeric|min:0|max:1000',
            'deadline' => 'required|date|after:now',
            'attachments.*' => 'nullable|file|max:5120', // 5MB max
        ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('assignments/' . $classId, 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        $assignment = Assignment::create([
            'class_id' => $classId,
            'title' => $request->title,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'total_marks' => $request->total_marks,
            'deadline' => $request->deadline,
            'attachments' => $attachments,
            'status' => 'published',
        ]);

        // TODO: Send notifications to enrolled students

        return redirect()->route('admin.assignments.show', [$classId, $assignment->id])
            ->with('success', 'Assignment created successfully!');
    }

    public function show($classId, $assignmentId)
    {
        $class = ClassModel::findOrFail($classId);
        $user = Auth::user();

        $this->checkClassAccess($class, $user);

        $assignment = Assignment::with([
            'class',
            'submissions' => function ($query) use ($user) {
                if ($user->hasRole('student')) {
                    $query->where('student_id', $user->id);
                }
            }
        ])->findOrFail($assignmentId);

        $submission = null;
        $allSubmissions = null;

        if ($user->hasRole('student')) {
            $submission = $assignment->submissions->first();
        } elseif ($user->hasRole('faculty') && $class->faculty_id === $user->id) {
            $allSubmissions = $assignment->submissions()
                ->with('student')
                ->paginate(10);

            // Convert to array format
            $allSubmissionsData = [
                'data' => $allSubmissions->items(),
                'links' => $allSubmissions->linkCollection()->toArray(),
            ];
        }

        // Send class as array, not object
        $classData = [
            'id' => (int) $class->id,
            'name' => (string) $class->name,
            'subject_code' => (string) $class->subject_code,
            'semester' => (int) $class->semester,
            'section' => (string) $class->section,
        ];

        return Inertia::render('admin/Assignments/Show', [
            'class' => $classData,
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'instructions' => $assignment->instructions,
                'attachments' => $assignment->attachments,
                'total_marks' => $assignment->total_marks,
                'deadline' => $assignment->deadline,
                'status' => $assignment->status,
                'submissions_count' => $assignment->submissions_count,
            ],
            'isFaculty' => $class->faculty_id === $user->id,
            'submission' => $submission,
            'allSubmissions' => $allSubmissionsData ?? [],
        ]);
    }

    public function updateStatus(Request $request, $classId, $assignmentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $class = ClassModel::findOrFail($classId);

        if ($class->faculty_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:draft,published,closed',
        ]);

        $assignment->update(['status' => $request->status]);

        return back()->with('success', 'Assignment status updated!');
    }

    public function viewSubmission($classId, $assignmentId, $submissionId)
    {
        $class = ClassModel::findOrFail($classId);
        $user = Auth::user();

        // Check if user is faculty member of this class
        if (!$user->hasRole('faculty') || $class->faculty_id !== $user->id) {
            abort(403);
        }

        // Get assignment details with attachments
        $assignment = Assignment::findOrFail($assignmentId);

        // Get submission with student details
        $submission = AssignmentSubmission::with('student')
            ->where('id', $submissionId)
            ->where('assignment_id', $assignmentId)
            ->firstOrFail();

        // Format submission attachments with URLs
        $formattedSubmissionAttachments = [];
        if ($submission->attachments && is_array($submission->attachments)) {
            foreach ($submission->attachments as $attachment) {
                $formattedSubmissionAttachments[] = [
                    'name' => $attachment['name'] ?? basename($attachment['path']),
                    'path' => $attachment['path'],
                    'size' => $attachment['size'] ?? 0,
                    'url' => Storage::url($attachment['path']),
                    'original_name' => $attachment['name'] ?? basename($attachment['path']),
                    'mime_type' => Storage::mimeType($attachment['path']) ?? 'application/octet-stream',
                ];
            }
        }

        // Format assignment attachments with URLs
        $formattedAssignmentAttachments = [];
        if ($assignment->attachments && is_array($assignment->attachments)) {
            foreach ($assignment->attachments as $attachment) {
                $formattedAssignmentAttachments[] = [
                    'name' => $attachment['name'] ?? basename($attachment['path']),
                    'path' => $attachment['path'],
                    'size' => $attachment['size'] ?? 0,
                    'url' => Storage::url($attachment['path']),
                    'original_name' => $attachment['name'] ?? basename($attachment['path']),
                    'mime_type' => Storage::mimeType($attachment['path']) ?? 'application/octet-stream',
                ];
            }
        }

        // Get next and previous submission IDs for navigation
        $submissionIds = AssignmentSubmission::where('assignment_id', $assignmentId)
            ->orderBy('created_at', 'asc')
            ->pluck('id')
            ->toArray();

        $currentIndex = array_search($submissionId, $submissionIds);
        $nextSubmissionId = $submissionIds[$currentIndex + 1] ?? null;
        $prevSubmissionId = $submissionIds[$currentIndex - 1] ?? null;

        return Inertia::render('admin/Assignments/ViewSubmission', [
            'classData' => [
                'id' => (int) $class->id,
                'name' => (string) $class->name,
                'subject_code' => (string) $class->subject_code,
                'semester' => (int) $class->semester,
                'section' => (string) $class->section,
            ],
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'instructions' => $assignment->instructions,
                'total_marks' => $assignment->total_marks,
                'deadline' => $assignment->deadline,
                'status' => $assignment->status,
                'attachments' => $formattedAssignmentAttachments,
            ],
            'submission' => [
                'id' => $submission->id,
                'student' => [
                    'id' => $submission->student->id,
                    'name' => $submission->student->name,
                    'email' => $submission->student->email,
                    'roll_number' => $submission->student->roll_number ?? null,
                ],
                'submitted_at' => $submission->created_at->toISOString(),
                'updated_at' => $submission->updated_at->toISOString(),
                'content' => $submission->content,
                'attachments' => $formattedSubmissionAttachments,
                'marks_obtained' => $submission->marks_obtained,
                'feedback' => $submission->feedback,
                'status' => $submission->status,
                'late_submission' => $submission->late_submission ?? false,
            ],
            'navigation' => [
                'next_submission_id' => $nextSubmissionId,
                'prev_submission_id' => $prevSubmissionId,
                'total_submissions' => count($submissionIds),
                'current_position' => $currentIndex + 1,
            ],
        ]);
    }

    private function checkClassAccess($class, $user)
    {
        if ($user->hasRole('faculty') && $class->faculty_id === $user->id) {
            return true;
        }

        if ($user->hasRole('student')) {
            $isEnrolled = $class->enrollments()
                ->where('student_id', $user->id)
                ->where('status', 'active')
                ->exists();

            if ($isEnrolled) {
                return true;
            }
        }

        abort(403);
    }

    public function updateSubmission(Request $request, $classId, $assignmentId, $submissionId)
    {
        $class = ClassModel::findOrFail($classId);
        $user = Auth::user();

        // Check if user is faculty member of this class
        if (!$user->hasRole('faculty') || $class->faculty_id !== $user->id) {
            abort(403);
        }

        $submission = AssignmentSubmission::where('id', $submissionId)
            ->where('assignment_id', $assignmentId)
            ->firstOrFail();

        $validated = $request->validate([
            'marks_obtained' => ['required', 'numeric', 'min:0', 'max:1000'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'marks_obtained' => $validated['marks_obtained'],
            'feedback' => $validated['feedback'],
            'status' => 'graded',
            'graded_at' => now(),
        ]);

        return back()->with('success', 'Grade updated successfully.');
    }
}