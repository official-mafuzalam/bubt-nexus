<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function submit(Request $request, $classId, $assignmentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $user = Auth::user();
        
        // Check if assignment is still open
        if ($assignment->deadline < now() && $assignment->status !== 'closed') {
            $assignment->update(['status' => 'closed']);
        }
        
        if ($assignment->status === 'closed') {
            return back()->with('error', 'Assignment deadline has passed.');
        }
        
        // Check if student is enrolled
        $isEnrolled = $assignment->class->enrollments()
            ->where('student_id', $user->id)
            ->where('status', 'active')
            ->exists();
        
        if (!$isEnrolled) {
            abort(403);
        }
        
        $request->validate([
            'content' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);
        
        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('submissions/' . $assignmentId, 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }
        
        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignmentId,
                'student_id' => $user->id,
            ],
            [
                'content' => $request->content,
                'attachments' => $attachments,
                'submitted_at' => now(),
                'status' => $assignment->isLateSubmission(now()) ? 'late' : 'submitted',
            ]
        );
        
        return back()->with('success', 'Assignment submitted successfully!');
    }
    
    public function grade(Request $request, $submissionId)
    {
        $submission = AssignmentSubmission::with('assignment')->findOrFail($submissionId);
        $assignment = $submission->assignment;
        
        // Only faculty of the class can grade
        if ($assignment->class->faculty_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'marks_obtained' => 'required|numeric|min:0|max:' . $assignment->total_marks,
            'feedback' => 'nullable|string',
        ]);
        
        $submission->update([
            'marks_obtained' => $request->marks_obtained,
            'feedback' => $request->feedback,
            'status' => 'graded',
        ]);
        
        // TODO: Send notification to student about grade
        
        return back()->with('success', 'Grade submitted successfully!');
    }
    
    public function downloadAttachment($submissionId, $index)
    {
        $submission = AssignmentSubmission::findOrFail($submissionId);
        $user = Auth::user();
        
        // Check access
        if ($submission->student_id !== $user->id && 
            $submission->assignment->class->faculty_id !== $user->id) {
            abort(403);
        }
        
        $attachments = $submission->attachments;
        if (!isset($attachments[$index])) {
            abort(404);
        }
        
        $attachment = $attachments[$index];
        return Storage::disk('public')->download($attachment['path'], $attachment['name']);
    }
}