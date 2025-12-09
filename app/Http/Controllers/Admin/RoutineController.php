<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoutineController extends Controller
{
    /**
     * Display a listing of the resource with filters.
     */
    public function index(Request $request)
    {
        // Get filter parameters from request
        $filters = [
            'program_id' => $request->input('program_id'),
            'intake' => $request->input('intake'),
            'section' => $request->input('section'),
            'semester' => $request->input('semester'),
            'day' => $request->input('day'),
            'teacher_code' => $request->input('teacher_code'),
            'course_code' => $request->input('course_code'),
            'room_number' => $request->input('room_number'),
            'status' => $request->input('status', 'active'),
        ];

        // Build query with filters
        $query = ClassRoutine::query()->with('program');

        // Apply filters
        if ($filters['program_id']) {
            $query->where('program_id', $filters['program_id']);
        }

        if ($filters['intake']) {
            $query->where('intake', $filters['intake']);
        }

        if ($filters['section']) {
            $query->where('section', $filters['section']);
        }

        if ($filters['semester']) {
            $query->where('semester', 'like', '%' . $filters['semester'] . '%');
        }

        if ($filters['day']) {
            $query->where('day', strtoupper($filters['day']));
        }

        if ($filters['teacher_code']) {
            $query->where('teacher_code', 'like', '%' . $filters['teacher_code'] . '%');
        }

        if ($filters['course_code']) {
            $query->where('course_code', 'like', '%' . $filters['course_code'] . '%');
        }

        if ($filters['room_number']) {
            $query->where('room_number', 'like', '%' . $filters['room_number'] . '%');
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Order by program, intake, section, day, and time
        $routines = $query->orderBy('program_id')
            ->orderBy('intake')
            ->orderBy('section')
            ->orderByRaw("FIELD(day, 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN')")
            ->orderBy('start_time')
            ->paginate(50)
            ->withQueryString();

        // Get filter options for dropdowns
        $programs = Program::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get unique values for filters
        $filterOptions = [
            'intakes' => ClassRoutine::distinct()->orderBy('intake', 'desc')->pluck('intake'),
            'sections' => ClassRoutine::distinct()->orderBy('section')->pluck('section'),
            'semesters' => ClassRoutine::distinct()->orderBy('semester', 'desc')->pluck('semester'),
            'days' => ClassRoutine::distinct()->orderByRaw("FIELD(day, 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN')")->pluck('day'),
            'teacher_codes' => ClassRoutine::distinct()->orderBy('teacher_code')->pluck('teacher_code'),
            'course_codes' => ClassRoutine::distinct()->orderBy('course_code')->pluck('course_code'),
            'room_numbers' => ClassRoutine::distinct()->orderBy('room_number')->pluck('room_number'),
            'statuses' => ['active', 'cancelled', 'rescheduled'],
        ];

        // Get statistics
        $stats = [
            'total_routines' => ClassRoutine::count(),
            'active_routines' => ClassRoutine::where('status', 'active')->count(),
            'programs_with_routines' => ClassRoutine::distinct('program_id')->count('program_id'),
            'unique_intakes' => ClassRoutine::distinct()->select('intake', 'section')->count(),
        ];

        return Inertia::render('admin/Routines/Index', [
            'routines' => $routines,
            'programs' => $programs,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $programs = Program::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        $statuses = ['active', 'cancelled', 'rescheduled'];

        // Get time slots in the format the Vue component expects
        $timeSlots = [
            [
                'id' => 1,
                'slot_name' => '08:00 AM to 09:15 AM',
                'start_time' => '08:00:00',
                'end_time' => '09:15:00'
            ],
            [
                'id' => 2,
                'slot_name' => '09:15 AM to 10:30 AM',
                'start_time' => '09:15:00',
                'end_time' => '10:30:00'
            ],
            [
                'id' => 3,
                'slot_name' => '10:30 AM to 11:45 AM',
                'start_time' => '10:30:00',
                'end_time' => '11:45:00'
            ],
            [
                'id' => 4,
                'slot_name' => '11:45 AM to 01:00 PM',
                'start_time' => '11:45:00',
                'end_time' => '13:00:00'
            ],
            [
                'id' => 5,
                'slot_name' => '01:30 PM to 02:45 PM',
                'start_time' => '13:30:00',
                'end_time' => '14:45:00'
            ],
            [
                'id' => 6,
                'slot_name' => '02:45 PM to 04:00 PM',
                'start_time' => '14:45:00',
                'end_time' => '16:00:00'
            ],
            [
                'id' => 7,
                'slot_name' => '04:00 PM to 05:15 PM',
                'start_time' => '16:00:00',
                'end_time' => '17:15:00'
            ],
            [
                'id' => 8,
                'slot_name' => '05:15 PM to 06:30 PM',
                'start_time' => '17:15:00',
                'end_time' => '18:30:00'
            ],
            [
                'id' => 9,
                'slot_name' => '08:20 AM to 09:30 AM',
                'start_time' => '08:20:00',
                'end_time' => '09:30:00'
            ],
            [
                'id' => 10,
                'slot_name' => '09:30 AM to 10:40 AM',
                'start_time' => '09:30:00',
                'end_time' => '10:40:00'
            ],
            [
                'id' => 11,
                'slot_name' => '10:40 AM to 11:50 AM',
                'start_time' => '10:40:00',
                'end_time' => '11:50:00'
            ],
            [
                'id' => 12,
                'slot_name' => '11:50 AM to 01:00 PM',
                'start_time' => '11:50:00',
                'end_time' => '13:00:00'
            ],
            [
                'id' => 13,
                'slot_name' => '03:30 PM to 04:40 PM',
                'start_time' => '15:30:00',
                'end_time' => '16:40:00'
            ],
            [
                'id' => 14,
                'slot_name' => '04:40 PM to 05:50 PM',
                'start_time' => '16:40:00',
                'end_time' => '17:50:00'
            ],
            [
                'id' => 15,
                'slot_name' => '05:50 PM to 07:00 PM',
                'start_time' => '17:50:00',
                'end_time' => '19:00:00'
            ],
            [
                'id' => 16,
                'slot_name' => '07:00 PM to 08:10 PM',
                'start_time' => '19:00:00',
                'end_time' => '20:10:00'
            ],
            [
                'id' => 17,
                'slot_name' => '08:10 PM to 09:20 PM',
                'start_time' => '20:10:00',
                'end_time' => '21:20:00'
            ],
        ];

        return Inertia::render('admin/Routines/Create', [
            'programs' => $programs,
            'days' => $days,
            'statuses' => $statuses,
            'timeSlots' => $timeSlots,
            'semesters' => array_keys(\App\Http\Class\Data::getSemesterOptions()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'intake' => 'required|integer|min:1',
            'section' => 'required|integer|min:1',
            'semester' => 'required|string|max:50',
            'day' => 'required|string|in:MON,TUE,WED,THU,FRI,SAT,SUN',
            'time_slot' => 'required|string|max:50',
            'course_code' => 'required|string|max:20',
            'course_name' => 'nullable|string|max:100',
            'teacher_code' => 'required|string|max:10',
            'teacher_name' => 'nullable|string|max:100',
            'room_number' => 'required|string|max:20',
            'room_type' => 'nullable|string|max:20',
            'status' => 'required|in:active,cancelled,rescheduled',
            'effective_date' => 'nullable|date',
            'slot_order' => 'nullable|integer|min:1',
        ]);

        // Parse time slot to get start_time and end_time
        $timeInfo = ClassRoutine::parseTimeSlot($validated['time_slot']);

        // Format class details
        $classDetails = "{$validated['course_code']}: {$validated['teacher_code']} R: {$validated['room_number']}";

        // Create the routine
        $routine = ClassRoutine::create([
            'program_id' => $validated['program_id'],
            'intake' => $validated['intake'],
            'section' => $validated['section'],
            'semester' => $validated['semester'],
            'day' => $validated['day'],
            'time_slot' => $validated['time_slot'],
            'start_time' => $timeInfo['start_time'] ? date('H:i:s', strtotime($timeInfo['start_time'])) : null,
            'end_time' => $timeInfo['end_time'] ? date('H:i:s', strtotime($timeInfo['end_time'])) : null,
            'course_code' => $validated['course_code'],
            'course_name' => $validated['course_name'],
            'teacher_code' => $validated['teacher_code'],
            'teacher_name' => $validated['teacher_name'],
            'room_number' => $validated['room_number'],
            'room_type' => $validated['room_type'] ?? 'Classroom',
            'class_details' => $classDetails,
            'status' => $validated['status'],
            'effective_date' => $validated['effective_date'],
            'slot_order' => $validated['slot_order'] ?? 1,
        ]);

        return redirect()->route('admin.routines.index')
            ->with('success', 'Class routine created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $routine = ClassRoutine::with('program')->findOrFail($id);

        // Calculate program stats
        $programStats = [
            'total_routines' => ClassRoutine::where('program_id', $routine->program_id)->count(),
            'active_routines' => ClassRoutine::where('program_id', $routine->program_id)
                ->where('status', 'active')
                ->count(),
            'unique_courses' => ClassRoutine::where('program_id', $routine->program_id)
                ->distinct('course_code')
                ->count(),
            'unique_teachers' => ClassRoutine::where('program_id', $routine->program_id)
                ->distinct('teacher_code')
                ->count(),
        ];

        return Inertia::render('admin/Routines/Show', [
            'routine' => $routine,
            'program_stats' => $programStats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $routine = ClassRoutine::findOrFail($id);

        $programs = Program::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        $statuses = ['active', 'cancelled', 'rescheduled'];

        // Get time slots in the format the Vue component expects
        $timeSlots = [
            [
                'id' => 1,
                'slot_name' => '08:00 AM to 09:15 AM',
                'start_time' => '08:00:00',
                'end_time' => '09:15:00'
            ],
            [
                'id' => 2,
                'slot_name' => '09:15 AM to 10:30 AM',
                'start_time' => '09:15:00',
                'end_time' => '10:30:00'
            ],
            [
                'id' => 3,
                'slot_name' => '10:30 AM to 11:45 AM',
                'start_time' => '10:30:00',
                'end_time' => '11:45:00'
            ],
            [
                'id' => 4,
                'slot_name' => '11:45 AM to 01:00 PM',
                'start_time' => '11:45:00',
                'end_time' => '13:00:00'
            ],
            [
                'id' => 5,
                'slot_name' => '01:30 PM to 02:45 PM',
                'start_time' => '13:30:00',
                'end_time' => '14:45:00'
            ],
            [
                'id' => 6,
                'slot_name' => '02:45 PM to 04:00 PM',
                'start_time' => '14:45:00',
                'end_time' => '16:00:00'
            ],
            [
                'id' => 7,
                'slot_name' => '04:00 PM to 05:15 PM',
                'start_time' => '16:00:00',
                'end_time' => '17:15:00'
            ],
            [
                'id' => 8,
                'slot_name' => '05:15 PM to 06:30 PM',
                'start_time' => '17:15:00',
                'end_time' => '18:30:00'
            ],
            [
                'id' => 9,
                'slot_name' => '08:20 AM to 09:30 AM',
                'start_time' => '08:20:00',
                'end_time' => '09:30:00'
            ],
            [
                'id' => 10,
                'slot_name' => '09:30 AM to 10:40 AM',
                'start_time' => '09:30:00',
                'end_time' => '10:40:00'
            ],
            [
                'id' => 11,
                'slot_name' => '10:40 AM to 11:50 AM',
                'start_time' => '10:40:00',
                'end_time' => '11:50:00'
            ],
            [
                'id' => 12,
                'slot_name' => '11:50 AM to 01:00 PM',
                'start_time' => '11:50:00',
                'end_time' => '13:00:00'
            ],
            [
                'id' => 13,
                'slot_name' => '03:30 PM to 04:40 PM',
                'start_time' => '15:30:00',
                'end_time' => '16:40:00'
            ],
            [
                'id' => 14,
                'slot_name' => '04:40 PM to 05:50 PM',
                'start_time' => '16:40:00',
                'end_time' => '17:50:00'
            ],
            [
                'id' => 15,
                'slot_name' => '05:50 PM to 07:00 PM',
                'start_time' => '17:50:00',
                'end_time' => '19:00:00'
            ],
            [
                'id' => 16,
                'slot_name' => '07:00 PM to 08:10 PM',
                'start_time' => '19:00:00',
                'end_time' => '20:10:00'
            ],
            [
                'id' => 17,
                'slot_name' => '08:10 PM to 09:20 PM',
                'start_time' => '20:10:00',
                'end_time' => '21:20:00'
            ],
        ];

        return Inertia::render('admin/Routines/Edit', [
            'routine' => $routine,
            'programs' => $programs,
            'days' => $days,
            'statuses' => $statuses,
            'timeSlots' => $timeSlots,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $routine = ClassRoutine::findOrFail($id);

        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'intake' => 'required|integer|min:1',
            'section' => 'required|integer|min:1',
            'semester' => 'required|string|max:50',
            'day' => 'required|string|in:MON,TUE,WED,THU,FRI,SAT,SUN',
            'time_slot' => 'required|string|max:50',
            'course_code' => 'required|string|max:20',
            'course_name' => 'nullable|string|max:100',
            'teacher_code' => 'required|string|max:10',
            'teacher_name' => 'nullable|string|max:100',
            'room_number' => 'required|string|max:20',
            'room_type' => 'nullable|string|max:20',
            'status' => 'required|in:active,cancelled,rescheduled',
            'effective_date' => 'nullable|date',
            'slot_order' => 'nullable|integer|min:1',
        ]);

        // Parse time slot to get start_time and end_time
        $timeInfo = ClassRoutine::parseTimeSlot($validated['time_slot']);

        // Format class details
        $classDetails = "{$validated['course_code']}: {$validated['teacher_code']} R: {$validated['room_number']}";

        // Update the routine
        $routine->update([
            'program_id' => $validated['program_id'],
            'intake' => $validated['intake'],
            'section' => $validated['section'],
            'semester' => $validated['semester'],
            'day' => $validated['day'],
            'time_slot' => $validated['time_slot'],
            'start_time' => $timeInfo['start_time'] ? date('H:i:s', strtotime($timeInfo['start_time'])) : null,
            'end_time' => $timeInfo['end_time'] ? date('H:i:s', strtotime($timeInfo['end_time'])) : null,
            'course_code' => $validated['course_code'],
            'course_name' => $validated['course_name'],
            'teacher_code' => $validated['teacher_code'],
            'teacher_name' => $validated['teacher_name'],
            'room_number' => $validated['room_number'],
            'room_type' => $validated['room_type'] ?? 'Classroom',
            'class_details' => $classDetails,
            'status' => $validated['status'],
            'effective_date' => $validated['effective_date'],
            'slot_order' => $validated['slot_order'] ?? $routine->slot_order,
        ]);

        return redirect()->route('admin.routines.index')
            ->with('success', 'Class routine updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $routine = ClassRoutine::findOrFail($id);
        $routine->delete();

        return redirect()->route('admin.routines.index')
            ->with('success', 'Class routine deleted successfully.');
    }

    /**
     * Bulk delete routines
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:class_routines,id',
        ]);

        ClassRoutine::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.routines.index')
            ->with('success', 'Selected routines deleted successfully.');
    }

    /**
     * Export routines to CSV
     */
    public function export(Request $request)
    {
        // Apply same filters as index
        $filters = $request->only(['program_id', 'intake', 'section', 'semester', 'day', 'status']);

        $query = ClassRoutine::query()->with('program');

        foreach ($filters as $key => $value) {
            if ($value) {
                if ($key === 'program_id') {
                    $query->where('program_id', $value);
                } elseif ($key === 'day') {
                    $query->where('day', strtoupper($value));
                } elseif ($key === 'status') {
                    $query->where('status', $value);
                } else {
                    $query->where($key, $value);
                }
            }
        }

        $routines = $query->orderBy('program_id')
            ->orderBy('intake')
            ->orderBy('section')
            ->orderByRaw("FIELD(day, 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN')")
            ->orderBy('start_time')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="routines_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($routines) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // Add headers
            fputcsv($file, [
                'Program',
                'Intake',
                'Section',
                'Semester',
                'Day',
                'Time Slot',
                'Course Code',
                'Course Name',
                'Teacher Code',
                'Teacher Name',
                'Room Number',
                'Room Type',
                'Status',
                'Effective Date',
            ]);

            // Add data
            foreach ($routines as $routine) {
                fputcsv($file, [
                    $routine->program->name ?? '',
                    $routine->intake,
                    $routine->section,
                    $routine->semester,
                    $routine->day,
                    $routine->time_slot,
                    $routine->course_code,
                    $routine->course_name ?? '',
                    $routine->teacher_code,
                    $routine->teacher_name ?? '',
                    $routine->room_number,
                    $routine->room_type ?? 'Classroom',
                    $routine->status,
                    $routine->effective_date ? $routine->effective_date->format('Y-m-d') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get filter options via AJAX
     */
    public function getFilterOptions(Request $request)
    {
        $query = ClassRoutine::query();

        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->intake) {
            $query->where('intake', $request->intake);
        }

        return response()->json([
            'sections' => $query->distinct()->orderBy('section')->pluck('section'),
            'semesters' => $query->distinct()->orderBy('semester', 'desc')->pluck('semester'),
            'days' => $query->distinct()->orderByRaw("FIELD(day, 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN')")->pluck('day'),
            'teacher_codes' => $query->distinct()->orderBy('teacher_code')->pluck('teacher_code'),
            'course_codes' => $query->distinct()->orderBy('course_code')->pluck('course_code'),
            'room_numbers' => $query->distinct()->orderBy('room_number')->pluck('room_number'),
        ]);
    }

    /**
     * Display routines for the authenticated user
     */
    public function myRoutines(Request $request)
    {
        $user = $request->user();
        $program = $user->UserDetail->program_id;
        $intake = $user->UserDetail->intake;
        $section = $user->UserDetail->section;
        $semester = $user->UserDetail->current_semester;

        $routines = ClassRoutine::where('program_id', $program)
            ->where('intake', $intake)
            ->where('section', $section)
            ->where('semester', 'like', '%' . $semester . '%')
            ->orderByRaw("FIELD(day, 'SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI')")
            ->orderBy('start_time')
            ->get();

        // Log::info('My Routines', ['user_id' => $user->id, 'program_id' => $program, 'intake' => $intake, 'section' => $section, 'semester' => $semester, 'routines' => $routines, 'routines_count' => $routines->count()]);
        return Inertia::render('admin/Routines/MyRoutines', [
            'routines' => $routines,
        ]);
    }
}