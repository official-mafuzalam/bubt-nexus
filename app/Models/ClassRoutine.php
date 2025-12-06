<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoutine extends Model
{
    use HasFactory;

    protected $table = 'class_routines';

    protected $fillable = [
        'program_id',
        'intake',
        'section',
        'semester',
        'day',
        'time_slot',
        'start_time',
        'end_time',
        'course_code',
        'course_name',
        'teacher_code',
        'teacher_name',
        'room_number',
        'room_type',
        'status',
        'effective_date',
        'slot_order',
        'class_details',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'effective_date' => 'date',
        'intake' => 'integer',
        'section' => 'integer',
        'slot_order' => 'integer',
    ];

    protected $appends = [
        'formatted_time',
        'intake_full',
        'course_teacher_room',
    ];

    /**
     * Get the program that owns the class routine.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Scope a query to only include active routines.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query for specific program and intake.
     */
    public function scopeForIntake($query, $programId, $intake, $section = null, $semester = null)
    {
        $query = $query->where('program_id', $programId)
            ->where('intake', $intake);

        if ($section) {
            $query = $query->where('section', $section);
        }

        if ($semester) {
            $query = $query->where('semester', 'like', "%$semester%");
        }

        return $query;
    }

    /**
     * Scope a query for specific day.
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day', strtoupper($day));
    }

    /**
     * Scope a query for specific teacher.
     */
    public function scopeForTeacher($query, $teacherCode)
    {
        return $query->where('teacher_code', $teacherCode);
    }

    /**
     * Scope a query for specific room.
     */
    public function scopeForRoom($query, $roomNumber)
    {
        return $query->where('room_number', $roomNumber);
    }

    /**
     * Scope a query for specific course.
     */
    public function scopeForCourse($query, $courseCode)
    {
        return $query->where('course_code', $courseCode);
    }

    /**
     * Order query by day and time.
     */
    public function scopeOrderByDayTime($query)
    {
        return $query->orderByRaw("
            FIELD(day, 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'),
            start_time,
            slot_order
        ");
    }

    /**
     * Get the formatted time attribute.
     */
    public function getFormattedTimeAttribute()
    {
        return date('h:i A', strtotime($this->start_time)) . ' to ' .
            date('h:i A', strtotime($this->end_time));
    }

    /**
     * Get the full intake with section.
     */
    public function getIntakeFullAttribute()
    {
        return $this->intake . ($this->section ? ' - ' . $this->section : '');
    }

    /**
     * Get course, teacher, and room in a formatted string.
     */
    public function getCourseTeacherRoomAttribute()
    {
        return "{$this->course_code}: {$this->teacher_code} R: {$this->room_number}";
    }

    /**
     * Check if the routine is currently active (based on effective_date).
     */
    public function getIsCurrentlyActiveAttribute()
    {
        if (!$this->effective_date) {
            return true;
        }

        return $this->effective_date <= now()->toDateString();
    }

    /**
     * Parse intake string like "50 - 1" into intake and section.
     */
    public static function parseIntakeString($intakeString)
    {
        if (preg_match('/(\d+)\s*-\s*(\d+)/', $intakeString, $matches)) {
            return [
                'intake' => (int) $matches[1],
                'section' => (int) $matches[2]
            ];
        } elseif (preg_match('/(\d+)/', $intakeString, $matches)) {
            return [
                'intake' => (int) $matches[1],
                'section' => 1
            ];
        }

        return [
            'intake' => null,
            'section' => 1
        ];
    }

    /**
     * Parse semester string like "Fall, 2025".
     */
    public static function parseSemesterString($semesterString)
    {
        $parts = explode(',', $semesterString);
        return [
            'semester' => isset($parts[0]) ? trim($parts[0]) : null,
            'year' => isset($parts[1]) ? trim($parts[1]) : date('Y'),
        ];
    }

    /**
     * Parse time slot like "07:00 PM to 08:10 PM".
     */
    public static function parseTimeSlot($timeSlot)
    {
        if (preg_match('/(\d{1,2}:\d{2}\s*(?:AM|PM))\s*to\s*(\d{1,2}:\d{2}\s*(?:AM|PM))/i', $timeSlot, $matches)) {
            return [
                'start_time' => trim($matches[1]),
                'end_time' => trim($matches[2])
            ];
        }

        return [
            'start_time' => null,
            'end_time' => null
        ];
    }
    /**
     * Parse class details like "ELT 5105FC: KSI R: 1409".
     */
    public static function parseClassDetails($classDetails)
    {
        $parts = explode(':', $classDetails);
        $roomPart = isset($parts[2]) ? trim($parts[2]) : '';

        // Extract room number (remove "R: " prefix if present)
        $roomNumber = preg_replace('/^R:\s*/', '', $roomPart);

        return [
            'course_code' => isset($parts[0]) ? trim($parts[0]) : null,
            'teacher_code' => isset($parts[1]) ? trim($parts[1]) : null,
            'room_number' => $roomNumber,
        ];
    }

    /**
     * Create a routine from parsed data.
     */
    public static function createFromParsedData($programId, $data)
    {
        $intakeInfo = self::parseIntakeString($data['intake'] ?? '');
        $semesterInfo = self::parseSemesterString($data['semester'] ?? '');
        $classInfo = self::parseClassDetails($data['class_details'] ?? '');
        $timeInfo = self::parseTimeSlot($data['time_slot'] ?? '');

        return self::create([
            'program_id' => $programId,
            'intake' => $intakeInfo['intake'],
            'section' => $intakeInfo['section'],
            'semester' => trim($semesterInfo['semester'] . ', ' . $semesterInfo['year']),
            'day' => strtoupper($data['day'] ?? ''),
            'time_slot' => $data['time_slot'] ?? '',
            'start_time' => $timeInfo['start_time'] ? date('H:i:s', strtotime($timeInfo['start_time'])) : null,
            'end_time' => $timeInfo['end_time'] ? date('H:i:s', strtotime($timeInfo['end_time'])) : null,
            'course_code' => $classInfo['course_code'],
            'teacher_code' => $classInfo['teacher_code'],
            'room_number' => $classInfo['room_number'],
            'class_details' => $data['class_details'] ?? '',
            'slot_order' => $data['slot_order'] ?? 1,
            'status' => $data['status'] ?? 'active',
            'effective_date' => $data['effective_date'] ?? null,
        ]);
    }

    /**
     * Group routines by day for display.
     */
    public static function groupByDay($routines)
    {
        return $routines->groupBy('day')->map(function ($dayRoutines) {
            return $dayRoutines->sortBy('start_time')->values();
        })->sortBy(function ($value, $key) {
            $daysOrder = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
            return array_search($key, $daysOrder);
        });
    }

    /**
     * Get routines in JSON format similar to your input.
     */
    public static function toJsonFormat($routines)
    {
        $firstRoutine = $routines->first();

        if (!$firstRoutine) {
            return null;
        }

        $groupedRoutines = self::groupByDay($routines)->map(function ($dayRoutines) {
            $result = [
                'day' => $dayRoutines->first()->day,
            ];

            foreach ($dayRoutines as $routine) {
                $result[$routine->time_slot] = $routine->class_details;
            }

            return $result;
        })->values();

        return [
            'status' => true,
            'program' => $firstRoutine->program->code ?? '',
            'intake' => $firstRoutine->intake_full,
            'semester' => $firstRoutine->semester,
            'routine' => $groupedRoutines,
        ];
    }
}