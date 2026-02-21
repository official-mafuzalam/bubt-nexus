<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Note::query(); // Remove user filter

        // Apply filters
        if ($request->has('course_name') && $request->course_name) {
            $query->where('course_name', 'like', '%' . $request->course_name . '%');
        }

        if ($request->has('course_code') && $request->course_code) {
            $query->where('course_code', 'like', '%' . $request->course_code . '%');
        }

        if ($request->has('title') && $request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('file_type') && $request->file_type) {
            $query->where('file_type', $request->file_type);
        }

        // Add eager loading for user relationship and include user_id in the response
        $notes = $query->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(fn($note) => [
                'id' => $note->id,
                'title' => $note->title,
                'course_name' => $note->course_name,
                'course_code' => $note->course_code,
                'file_type' => $note->file_type,
                'file_url' => $note->file_url,
                'file_size' => $note->file_size,
                'created_at' => $note->created_at->toDateTimeString(),
                'formatted_size' => $this->formatFileSize($note->file_size),
                'file_icon' => $this->getFileIcon($note->file_type),
                'user_id' => $note->user_id, // Add the user_id of the note owner
                'user' => $note->user ? [
                    'name' => $note->user->name,
                    'email' => $note->user->email,
                ] : null,
            ]);

        // Get filter options (now from all notes)
        $filterOptions = [
            'course_names' => Note::distinct()
                ->pluck('course_name')
                ->filter()
                ->values(),
            'course_codes' => Note::distinct()
                ->pluck('course_code')
                ->filter()
                ->values(),
            'file_types' => Note::distinct()
                ->pluck('file_type')
                ->values(),
        ];

        // Get stats (now from all notes)
        $stats = [
            'total_notes' => Note::count(),
            'pdf_notes' => Note::where('file_type', 'pdf')->count(),
            'image_notes' => Note::where('file_type', 'image')->count(),
            'courses_with_notes' => Note::distinct('course_code')->count('course_code'),
            'total_contributors' => Note::distinct('user_id')->count('user_id'),
        ];

        return Inertia::render('admin/Notes/Index', [
            'notes' => $notes,
            'filters' => $request->only(['course_name', 'course_code', 'title', 'file_type']),
            'filterOptions' => $filterOptions,
            'stats' => $stats,
            'currentUserId' => auth()->id(), // Pass the current authenticated user ID
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/Notes/Create', [
            'max_file_size' => config('app.max_file_size', 10 * 1024 * 1024), // 10MB default
            'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'txt'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'course_name' => 'nullable|string|max:255',
            'course_code' => 'nullable|string|max:50',
            'file' => 'required|file|max:' . config('app.max_file_size', 10 * 1024 * 1024),
        ]);

        try {
            $file = $request->file('file');
            $fileSize = $file->getSize();
            $fileExtension = strtolower($file->getClientOriginalExtension());

            // Determine file type
            $fileType = $this->getFileTypeFromExtension($fileExtension);

            // Generate unique file name
            $fileName = Str::random(40) . '_' . time() . '.' . $fileExtension;
            $storagePath = 'notes/' . auth()->id();

            // Store file locally
            $filePath = $file->storeAs($storagePath, $fileName, 'public');
            $fileUrl = Storage::disk('public')->url($filePath);

            // Create note record
            Note::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'course_name' => $request->course_name,
                'course_code' => $request->course_code,
                'file_url' => $fileUrl,
                'storage_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
            ]);

            return redirect()->route('admin.notes.index')
                ->with('success', 'Note uploaded successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload note: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        // No user check - anyone can view
        return Inertia::render('admin/Notes/Show', [
            'note' => [
                'id' => $note->id,
                'title' => $note->title,
                'course_name' => $note->course_name,
                'course_code' => $note->course_code,
                'file_type' => $note->file_type,
                'file_url' => $note->file_url,
                'file_size' => $note->file_size,
                'formatted_size' => $this->formatFileSize($note->file_size),
                'created_at' => $note->created_at->toDateTimeString(),
                'updated_at' => $note->updated_at->toDateTimeString(),
                'user' => $note->user ? [
                    'name' => $note->user->name,
                    'email' => $note->user->email,
                ] : null,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        // Only allow editing if user owns the note or is admin
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        return Inertia::render('admin/Notes/Edit', [
            'note' => [
                'id' => $note->id,
                'title' => $note->title,
                'course_name' => $note->course_name,
                'course_code' => $note->course_code,
                'file_type' => $note->file_type,
                'file_url' => $note->file_url,
                'file_size' => $note->file_size,
                'formatted_size' => $this->formatFileSize($note->file_size),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        // Only allow updating if user owns the note or is admin
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'course_name' => 'nullable|string|max:255',
            'course_code' => 'nullable|string|max:50',
        ]);

        $note->update([
            'title' => $request->title,
            'course_name' => $request->course_name,
            'course_code' => $request->course_code,
        ]);

        return redirect()->route('admin.notes.index')
            ->with('success', 'Note updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        // Only allow deletion if user owns the note or is admin
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        try {
            // Delete from local storage
            if (Storage::disk('public')->exists($note->storage_path)) {
                Storage::disk('public')->delete($note->storage_path);
            }

            // Delete database record
            $note->delete();

            return redirect()->route('admin.notes.index')
                ->with('success', 'Note deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete note: ' . $e->getMessage());
        }
    }

    /**
     * Download the specified note.
     */
    public function download(Note $note)
    {
        // Anyone can download notes
        // Check if file exists
        if (!Storage::disk('public')->exists($note->storage_path)) {
            abort(404, 'File not found');
        }

        // Get file path and name
        $filePath = Storage::disk('public')->path($note->storage_path);
        $originalName = $note->title . '.' . pathinfo($note->storage_path, PATHINFO_EXTENSION);

        // Return file download response
        return response()->download($filePath, $originalName, [
            'Content-Type' => Storage::disk('public')->mimeType($note->storage_path),
        ]);
    }

    /**
     * Export notes to CSV.
     */
    public function export(Request $request)
    {
        $query = Note::where('user_id', auth()->id());

        // Apply filters if any
        if ($request->has('course_name') && $request->course_name) {
            $query->where('course_name', 'like', '%' . $request->course_name . '%');
        }

        if ($request->has('course_code') && $request->course_code) {
            $query->where('course_code', 'like', '%' . $request->course_code . '%');
        }

        if ($request->has('file_type') && $request->file_type) {
            $query->where('file_type', $request->file_type);
        }

        $notes = $query->orderBy('created_at', 'desc')->get();

        $csvFileName = 'notes_export_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];

        $callback = function () use ($notes) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'ID',
                'Title',
                'Course Name',
                'Course Code',
                'File Type',
                'File Size',
                'Created At',
                'File URL',
            ]);

            // Data
            foreach ($notes as $note) {
                fputcsv($file, [
                    $note->id,
                    $note->title,
                    $note->course_name,
                    $note->course_code,
                    strtoupper($note->file_type),
                    $this->formatFileSize($note->file_size),
                    $note->created_at->format('Y-m-d H:i:s'),
                    $note->file_url,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk delete notes.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'note_ids' => 'required|array',
            'note_ids.*' => 'exists:notes,id',
        ]);

        try {
            $notes = Note::whereIn('id', $request->note_ids)
                ->where('user_id', auth()->id())
                ->get();

            foreach ($notes as $note) {
                // Delete from local storage
                if (Storage::disk('public')->exists($note->storage_path)) {
                    Storage::disk('public')->delete($note->storage_path);
                }

                // Delete from database
                $note->delete();
            }

            return response()->json([
                'success' => true,
                'message' => count($notes) . ' notes deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Format file size.
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }

    /**
     * Helper: Get file icon name.
     */
    private function getFileIcon($fileType)
    {
        switch ($fileType) {
            case 'pdf':
                return 'FileText';
            case 'image':
                return 'Image';
            default:
                return 'File';
        }
    }

    /**
     * Helper: Get file type from extension.
     */
    private function getFileTypeFromExtension($extension)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $pdfExtensions = ['pdf'];

        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $pdfExtensions)) {
            return 'pdf';
        } else {
            return 'other';
        }
    }
}