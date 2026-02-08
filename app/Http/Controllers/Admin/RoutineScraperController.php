<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Program;
use App\Models\ClassRoutine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoutineScraperController extends Controller
{
    private $client;
    private $baseUrl = 'https://annex.bubt.edu.bd/global_file/routine.php';

    public function __construct()
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $this->client = new Client([
            'timeout' => 120,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ]
        ]);
    }

    /**
     * Scrape routine for a specific program code
     */
    public function scrapeProgram($programCode)
    {
        try {
            Log::info("=== Starting to scrape program: {$programCode} ===");

            $program = Program::where('code', $programCode)->first();
            if (!$program) {
                return response()->json([
                    'status' => false,
                    'error' => "Program with code {$programCode} not found in database"
                ], 404);
            }

            $url = $this->baseUrl . "?p={$programCode}&semNo=066";
            Log::info("Fetching URL: {$url}");

            $response = $this->client->get($url);
            $html = (string) $response->getBody();

            // Clear old routines for this program
            $this->clearOldRoutines($program->id);

            // Parse HTML directly (not as text)
            $result = $this->parseHtmlAndStore($html, $program);

            Log::info("=== Completed scraping program: {$programCode} ===");

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("Error scraping program {$programCode}: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'program_code' => $programCode,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear old routines for a program
     */
    private function clearOldRoutines($programId)
    {
        try {
            $deleted = ClassRoutine::where('program_id', $programId)->delete();
            Log::info("Cleared {$deleted} old routines for program ID: {$programId}");
        } catch (\Exception $e) {
            Log::warning("Error clearing old routines: " . $e->getMessage());
        }
    }

    /**
     * Parse HTML and store routines
     */
    private function parseHtmlAndStore($html, Program $program)
    {
        $crawler = new Crawler($html);

        // Find all tables with id="tableRtn" (these are the routine tables)
        $routineTables = $crawler->filter('table#tableRtn');
        Log::info("Found {$routineTables->count()} routine tables");

        if ($routineTables->count() === 0) {
            return [
                'status' => false,
                'message' => 'No routine tables found'
            ];
        }

        $intakesProcessed = 0;
        $totalRoutinesStored = 0;

        // Process each routine table
        foreach ($routineTables as $tableIndex => $table) {
            $tableCrawler = new Crawler($table);

            // Get the header table that contains program, intake, semester info
            // The header table is above each routine table
            $headerInfo = $this->extractHeaderInfo($tableCrawler);

            if (empty($headerInfo['intake'])) {
                Log::warning("Skipping table {$tableIndex}: No intake info found");
                continue;
            }

            Log::info("Processing intake: " . $headerInfo['intake'] . " - Semester: " . $headerInfo['semester']);

            // Parse course-faculty mappings for this intake
            $courseMappings = $this->extractCourseMappingsForIntake($tableCrawler, $headerInfo['intake']);
            Log::info("Found " . count($courseMappings) . " course mappings for intake " . $headerInfo['intake']);

            // Parse and store the routine table
            $routinesStored = $this->parseRoutineTable($tableCrawler, $program, $headerInfo, $courseMappings);

            $totalRoutinesStored += $routinesStored;
            $intakesProcessed++;

            Log::info("Stored {$routinesStored} routines for intake " . $headerInfo['intake']);
        }

        return [
            'status' => true,
            'program_id' => $program->id,
            'program_name' => $program->name,
            'intakes_processed' => $intakesProcessed,
            'routines_stored' => $totalRoutinesStored,
            'message' => "Successfully processed {$intakesProcessed} intakes with {$totalRoutinesStored} routine entries"
        ];
    }

    /**
     * Extract header information (program, intake, semester)
     */
    private function extractHeaderInfo(Crawler $tableCrawler)
    {
        $info = [
            'program' => '',
            'intake' => '',
            'semester' => ''
        ];

        try {
            // Go up to find the header table (usually has id="HdtableRtn")
            $currentNode = $tableCrawler->getNode(0);

            // Look for previous sibling tables
            while ($currentNode) {
                if (
                    $currentNode->nodeName === 'table' && $currentNode->hasAttribute('id') &&
                    $currentNode->getAttribute('id') === 'HdtableRtn'
                ) {

                    $headerCrawler = new Crawler($currentNode);
                    $headerText = $headerCrawler->text();

                    // Extract info from header text
                    if (preg_match('/Program:\s*([^\n]+)/i', $headerText, $matches)) {
                        $info['program'] = trim($matches[1]);
                    }
                    if (preg_match('/Intake:\s*([^\n]+)/i', $headerText, $matches)) {
                        $info['intake'] = trim($matches[1]);
                    }
                    if (preg_match('/Semester:\s*([^\n]+)/i', $headerText, $matches)) {
                        $info['semester'] = trim($matches[1]);
                    }

                    break;
                }
                $currentNode = $currentNode->previousSibling;
            }
        } catch (\Exception $e) {
            Log::warning("Error extracting header info: " . $e->getMessage());
        }

        return $info;
    }

    /**
     * Extract course-faculty mappings for an intake
     */
    private function extractCourseMappingsForIntake(Crawler $tableCrawler, $intake)
    {
        $mappings = [];

        try {
            // Look for the course mapping table after the routine table
            $currentNode = $tableCrawler->getNode(0)->nextSibling;

            while ($currentNode) {
                if ($currentNode->nodeName === 'table') {
                    $mappingCrawler = new Crawler($currentNode);
                    $tableText = $mappingCrawler->text();

                    // Check if this is a course mapping table
                    if (str_contains($tableText, 'Course Code') && str_contains($tableText, 'Faculty Code')) {
                        $rows = $mappingCrawler->filter('tr');

                        // Skip header row
                        for ($i = 1; $i < $rows->count(); $i++) {
                            $row = $rows->eq($i);
                            $cells = $row->filter('td');

                            if ($cells->count() >= 4) {
                                $courseCode = trim($cells->eq(0)->text());
                                $courseName = trim($cells->eq(1)->text());
                                $facultyCode = trim($cells->eq(2)->text());
                                $facultyName = trim($cells->eq(3)->text());

                                if (!empty($courseCode) && !empty($facultyCode)) {
                                    $mappings[$courseCode] = [
                                        'course_name' => $courseName,
                                        'faculty_code' => $facultyCode,
                                        'faculty_name' => $facultyName
                                    ];
                                }
                            }
                        }
                        break;
                    }
                }
                $currentNode = $currentNode->nextSibling;
            }
        } catch (\Exception $e) {
            Log::warning("Error extracting course mappings: " . $e->getMessage());
        }

        return $mappings;
    }

    /**
     * Parse a routine table
     */
    /**
     * Parse a routine table
     */
    private function parseRoutineTable(Crawler $table, Program $program, $headerInfo, $courseMappings)
    {
        $rows = $table->filter('tr');
        $routinesStored = 0;
        $batchData = [];

        if ($rows->count() < 2) {
            Log::warning("Table has too few rows: " . $rows->count());
            return 0;
        }

        // Extract time slots from header row (first row)
        $headerRow = $rows->first();
        $timeSlots = [];

        // Get all cells in header row (skip first which is "Day/Time")
        $headerCells = $headerRow->filter('td, th');
        for ($i = 1; $i < $headerCells->count(); $i++) {
            $timeSlot = trim($headerCells->eq($i)->text());
            if (!empty($timeSlot)) {
                $timeSlots[] = $timeSlot;
            }
        }

        Log::info("Found " . count($timeSlots) . " time slots: " . implode(', ', $timeSlots));

        // Define valid days
        $validDays = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THR', 'FRI', 'THU'];

        // Process each row (skip header row)
        for ($rowIndex = 1; $rowIndex < $rows->count(); $rowIndex++) {
            $row = $rows->eq($rowIndex);

            // Get day - check both <td> and <th> elements
            $day = '';

            // Try <th> first (days are often in header cells)
            $thCells = $row->filter('th');
            if ($thCells->count() > 0) {
                $day = trim($thCells->first()->text());
            }

            // If no <th>, try <td>
            if (empty($day)) {
                $tdCells = $row->filter('td');
                if ($tdCells->count() > 0) {
                    $day = trim($tdCells->first()->text());
                }
            }

            $day = strtoupper($day);

            // Skip if not a valid day
            if (!in_array($day, $validDays)) {
                Log::debug("Skipping row {$rowIndex}: Not a valid day - '{$day}'");
                continue;
            }

            Log::debug("Processing day: {$day}");

            // Get all cells for this row
            $cells = $row->filter('td');
            if ($cells->count() === 0) {
                $cells = $row->filter('th, td');
            }

            // Process each time slot column
            for ($colIndex = 1; $colIndex < $cells->count(); $colIndex++) {
                $timeSlotIndex = $colIndex - 1;

                if (!isset($timeSlots[$timeSlotIndex])) {
                    continue;
                }

                $timeSlot = $timeSlots[$timeSlotIndex];
                $cell = $cells->eq($colIndex);
                $cellText = trim($cell->text());

                if (!empty($cellText) && $cellText !== '&nbsp;' && $cellText !== ' ') {
                    Log::debug("Found class: Day={$day}, Time={$timeSlot}, Text='{$cellText}'");

                    $classes = $this->parseClassCell($cellText);

                    foreach ($classes as $classInfo) {
                        if (!empty($classInfo['course_code'])) {
                            Log::debug("Parsed class: {$classInfo['course_code']} - FC:{$classInfo['faculty_code']} - R:{$classInfo['room']}");

                            // Prepare data for batch insert
                            $routineData = $this->prepareRoutineData(
                                $program->id,
                                $headerInfo,
                                $day,
                                $timeSlot,
                                $classInfo,
                                $courseMappings
                            );

                            if ($routineData) {
                                $batchData[] = $routineData;
                                $routinesStored++;
                            }
                        } else {
                            Log::warning("Could not parse class from text: '{$cellText}'");
                        }
                    }
                }
            }
        }

        // Batch insert
        if (!empty($batchData)) {
            Log::info("Preparing to insert {$routinesStored} routines for intake {$headerInfo['intake']}");
            $this->batchInsertRoutines($batchData);
        } else {
            Log::warning("No routine data prepared for intake {$headerInfo['intake']}");
        }

        return $routinesStored;
    }

    /**
     * Parse individual class cell
     */
    private function parseClassCell($cellText)
    {
        $classes = [];

        // Clean up the text
        $cellText = strip_tags($cellText); // Remove HTML tags
        $cellText = preg_replace('/\s+/', ' ', $cellText); // Replace multiple spaces with single space
        $cellText = trim($cellText);

        if (empty($cellText)) {
            return $classes;
        }

        // Debug: Log what we're trying to parse
        Log::debug("Parsing cell text: '{$cellText}'");

        // Pattern for: "ICT 1102FC: JFM R: 4301"
        // This pattern handles the case where course code and FC: are together without space
        $pattern = '/^([A-Z]{2,}\s+\d+[A-Z]*)\s*FC:\s*([A-Z]+)\s+R:\s*(\d+)$/i';

        if (preg_match($pattern, $cellText, $matches)) {
            $courseCode = trim($matches[1]);
            $facultyCode = trim($matches[2]);
            $room = trim($matches[3]);

            Log::debug("Pattern matched: Course='{$courseCode}', Faculty='{$facultyCode}', Room='{$room}'");

            $classes[] = [
                'course_code' => $courseCode,
                'faculty_code' => $facultyCode,
                'room' => $room
            ];
        } else {
            // Try alternative patterns
            $alternativePatterns = [
                // Pattern: "ICT 1102 FC: JFM R: 4301" (with space)
                '/^([A-Z]{2,}\s+\d+[A-Z]*)\s+FC:\s*([A-Z]+)\s+R:\s*(\d+)$/i',

                // Pattern: "ICT 1102 FC:JFM R:4301" (no spaces after colons)
                '/^([A-Z]{2,}\s+\d+[A-Z]*)\s+FC:([A-Z]+)\s+R:(\d+)$/i',

                // Pattern: "ICT 1102FC:JFM R:4301" (no spaces anywhere)
                '/^([A-Z]{2,}\s+\d+[A-Z]*)FC:([A-Z]+)\s+R:(\d+)$/i',
            ];

            foreach ($alternativePatterns as $altPattern) {
                if (preg_match($altPattern, $cellText, $matches)) {
                    $courseCode = trim($matches[1]);
                    $facultyCode = trim($matches[2]);
                    $room = trim($matches[3]);

                    Log::debug("Alternative pattern matched: Course='{$courseCode}', Faculty='{$facultyCode}', Room='{$room}'");

                    $classes[] = [
                        'course_code' => $courseCode,
                        'faculty_code' => $facultyCode,
                        'room' => $room
                    ];
                    break;
                }
            }

            // If no pattern matched, try manual extraction
            if (empty($classes)) {
                Log::debug("No pattern matched, trying manual extraction");

                // Extract course code (should be at the beginning)
                if (preg_match('/^([A-Z]{2,}\s+\d+[A-Z]*)/', $cellText, $matches)) {
                    $courseCode = trim($matches[1]);

                    // Extract faculty code
                    $facultyCode = '';
                    if (preg_match('/FC:\s*([A-Z]+)/i', $cellText, $matches)) {
                        $facultyCode = trim($matches[1]);
                    }

                    // Extract room
                    $room = '';
                    if (preg_match('/R:\s*(\d+)/i', $cellText, $matches)) {
                        $room = trim($matches[1]);
                    }

                    Log::debug("Manual extraction: Course='{$courseCode}', Faculty='{$facultyCode}', Room='{$room}'");

                    $classes[] = [
                        'course_code' => $courseCode,
                        'faculty_code' => $facultyCode,
                        'room' => $room
                    ];
                }
            }
        }

        if (empty($classes)) {
            Log::warning("Failed to parse class from text: '{$cellText}'");
        }

        return $classes;
    }

    /**
     * Prepare routine data for insertion
     */
    private function prepareRoutineData($programId, $headerInfo, $day, $timeSlot, $classInfo, $courseMappings)
    {
        // Parse intake string (e.g., "49 - 1")
        $intakeParsed = ClassRoutine::parseIntakeString($headerInfo['intake']);

        if (!$intakeParsed['intake']) {
            Log::warning("Invalid intake format: " . $headerInfo['intake']);
            return null;
        }

        // Parse time slot
        $timeParsed = ClassRoutine::parseTimeSlot($timeSlot);

        if (!$timeParsed['start_time'] || !$timeParsed['end_time']) {
            Log::warning("Invalid time slot format: " . $timeSlot);
            return null;
        }

        // Get course details from mappings
        $courseCode = $classInfo['course_code'];
        $courseDetails = $courseMappings[$courseCode] ?? [
            'course_name' => null,
            'faculty_code' => $classInfo['faculty_code'],
            'faculty_name' => null
        ];

        // If faculty code from cell doesn't match mapping, use the one from cell
        if (
            !empty($classInfo['faculty_code']) &&
            (!empty($courseDetails['faculty_code']) && $classInfo['faculty_code'] !== $courseDetails['faculty_code'])
        ) {
            // Keep the cell's faculty code, but try to find name from other mappings
            foreach ($courseMappings as $mapping) {
                if ($mapping['faculty_code'] === $classInfo['faculty_code']) {
                    $courseDetails['faculty_name'] = $mapping['faculty_name'];
                    break;
                }
            }
        }

        // Format class details string
        $facultyCode = !empty($classInfo['faculty_code']) ? $classInfo['faculty_code'] : $courseDetails['faculty_code'];
        $classDetails = "{$courseCode}: {$facultyCode} R: {$classInfo['room']}";

        return [
            'program_id' => $programId,
            'intake' => $intakeParsed['intake'],
            'section' => $intakeParsed['section'],
            'semester' => $headerInfo['semester'] ?? 'Fall, 2025',
            'day' => $day,
            'time_slot' => $timeSlot,
            'start_time' => date('H:i:s', strtotime($timeParsed['start_time'])),
            'end_time' => date('H:i:s', strtotime($timeParsed['end_time'])),
            'course_code' => $courseCode,
            'course_name' => $courseDetails['course_name'],
            'teacher_code' => $facultyCode,
            'teacher_name' => $courseDetails['faculty_name'],
            'room_number' => $classInfo['room'],
            'room_type' => $this->determineRoomType($classInfo['room']),
            'class_details' => $classDetails,
            'status' => 'active',
            'effective_date' => now()->toDateString(),
            'slot_order' => $this->calculateSlotOrder($timeSlot),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Batch insert routines for better performance
     */
    private function batchInsertRoutines($data)
    {
        try {
            // Use chunking to avoid memory issues
            $chunks = array_chunk($data, 50);

            foreach ($chunks as $chunk) {
                DB::table('class_routines')->insert($chunk);
            }

            Log::info("Batch inserted " . count($data) . " routines");
        } catch (\Exception $e) {
            Log::error("Error batch inserting routines: " . $e->getMessage());

            // Fallback: insert one by one
            foreach ($data as $item) {
                try {
                    ClassRoutine::create($item);
                } catch (\Exception $e2) {
                    Log::error("Error inserting single routine: " . $e2->getMessage());
                }
            }
        }
    }

    /**
     * Determine room type based on room number
     */
    private function determineRoomType($roomNumber)
    {
        if (empty($roomNumber))
            return 'Classroom';

        $firstDigit = substr($roomNumber, 0, 1);
        return ($firstDigit == '4') ? 'Lab' : 'Classroom';
    }

    /**
     * Calculate slot order based on time
     */
    private function calculateSlotOrder($timeSlot)
    {
        $timeOrder = [
            '08:00 AM' => 1,
            '09:15 AM' => 2,
            '10:30 AM' => 3,
            '11:45 AM' => 4,
            '01:30 PM' => 5,
            '02:45 PM' => 6,
            '04:00 PM' => 7,
            '05:15 PM' => 8,
        ];

        if (preg_match('/(\d{1,2}:\d{2}\s*(?:AM|PM))/', $timeSlot, $matches)) {
            $startTime = $matches[1];
            return $timeOrder[$startTime] ?? 1;
        }

        return 1;
    }

    /**
     * Get database status
     */
    public function getStatus()
    {
        $stats = [
            'total_routines' => ClassRoutine::count(),
            'active_routines' => ClassRoutine::where('status', 'active')->count(),
            'programs_with_data' => ClassRoutine::distinct('program_id')->count('program_id'),
            'total_intakes' => DB::table('class_routines')
                ->select(DB::raw('CONCAT(intake, " - ", section) as intake_section'))
                ->distinct()
                ->count(),
            'last_updated' => ClassRoutine::max('updated_at'),
        ];

        return response()->json([
            'status' => true,
            'database_stats' => $stats,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}