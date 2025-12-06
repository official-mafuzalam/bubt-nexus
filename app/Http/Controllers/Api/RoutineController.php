<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutineController extends Controller
{
    public function getRoutine(Request $request)
    {
        $request->validate([
            'program' => 'required|string',
            'intake' => 'required|string',
            'section' => 'required|string',
        ]);

        try {
            // Parse intake and section
            $intakeInfo = ClassRoutine::parseIntakeString($request->intake);

            // Find the program by code
            $program = DB::table('programs')
                ->where('code', $request->program)
                ->orWhere('name', 'like', '%' . $request->program . '%')
                ->first();

            if (!$program) {
                return response()->json([
                    'status' => false,
                    'message' => 'Program not found',
                    'routine' => null,
                ], 404);
            }

            // Get routines for the specified program, intake, and section
            $routines = ClassRoutine::with('program')
                ->where('program_id', $program->id)
                ->where('intake', $intakeInfo['intake'])
                ->where('section', $intakeInfo['section'])
                ->active()
                ->orderByDayTime()
                ->get();

            if ($routines->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No routine found for the given criteria',
                    'program' => $program->code,
                    'intake' => $request->intake,
                    'section' => $intakeInfo['section'],
                    'routine' => [],
                ], 404);
            }

            // Group routines by day for display
            $groupedRoutines = ClassRoutine::groupByDay($routines)
                ->map(function ($dayRoutines) {
                    $result = [
                        'day' => $dayRoutines->first()->day,
                    ];

                    foreach ($dayRoutines as $routine) {
                        $result[$routine->time_slot] = $routine->class_details;
                    }

                    return $result;
                });

            // Sort days in order: SAT, SUN, MON, TUE, WED, THU, FRI
            $sortedRoutines = $groupedRoutines->sortBy(function ($value, $key) {
                // Define day order (note: using THR instead of THU as per your data)
                $daysOrder = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THR', 'FRI'];
                return array_search(strtoupper($key), $daysOrder);
            })->values();

            // Get the first routine to extract common info
            $firstRoutine = $routines->first();

            // Sort detailed_routines by day and time
            $sortedDetailedRoutines = $routines->sort(function ($a, $b) {
                // Define day order
                $daysOrder = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THR', 'FRI'];

                $aDayIndex = array_search(strtoupper($a->day), $daysOrder);
                $bDayIndex = array_search(strtoupper($b->day), $daysOrder);

                // First sort by day
                if ($aDayIndex !== $bDayIndex) {
                    return $aDayIndex - $bDayIndex;
                }

                // Then sort by start time
                return strtotime($a->start_time) - strtotime($b->start_time);
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Routine retrieved successfully',
                'program' => $program->code,
                'program_name' => $program->name,
                'intake' => $request->intake,
                'section' => $intakeInfo['section'],
                'semester' => $firstRoutine->semester,
                'routine' => $sortedRoutines,
                'detailed_routines' => $sortedDetailedRoutines->map(function ($routine) {
                    return [
                        'id' => $routine->id,
                        'day' => $routine->day,
                        'time_slot' => $routine->time_slot,
                        'formatted_time' => $routine->formatted_time,
                        'course_code' => $routine->course_code,
                        'course_name' => $routine->course_name,
                        'teacher_code' => $routine->teacher_code,
                        'teacher_name' => $routine->teacher_name,
                        'room_number' => $routine->room_number,
                        'room_type' => $routine->room_type,
                        'class_details' => $routine->class_details,
                        'status' => $routine->status,
                    ];
                }),
                'meta' => [
                    'total_classes' => $routines->count(),
                    'days_count' => $sortedRoutines->count(),
                    'unique_courses' => $routines->unique('course_code')->count(),
                    'unique_teachers' => $routines->unique('teacher_code')->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving routine: ' . $e->getMessage(),
                'routine' => null,
            ], 500);
        }
    }

    // Update the groupByDay method in your ClassRoutine model to sort properly
    /**
     * Group routines by day for display.
     */
    public static function groupByDay($routines)
    {
        return $routines->groupBy('day')->map(function ($dayRoutines) {
            return $dayRoutines->sortBy('start_time')->values();
        })->sortBy(function ($value, $key) {
            // Define day order (using THR instead of THU as per your data)
            $daysOrder = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THR', 'FRI'];
            return array_search(strtoupper($key), $daysOrder);
        });
    }

    // Also update the orderByDayTime scope in your ClassRoutine model:
    /**
     * Order query by day and time.
     */
    public function scopeOrderByDayTime($query)
    {
        // Using THR instead of THU as per your data
        return $query->orderByRaw("
            FIELD(day, 'SAT', 'SUN', 'MON', 'TUE', 'WED', 'THR', 'FRI'),
            start_time,
            slot_order
        ");
    }
}