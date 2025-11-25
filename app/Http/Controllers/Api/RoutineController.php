<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class RoutineController extends Controller
{
    public function getRoutine(Request $request)
    {
        $request->validate([
            'program' => 'required|string',
            'semester' => 'required|string',
            'intake' => 'required|string',
        ]);

        $program = $request->program;
        $semester = $request->semester;
        $intakeName = trim($request->intake);

        $url = "https://annex.bubt.edu.bd/global_file/routine.php?p={$program}&semNo={$semester}";

        try {
            $client = new Client();
            $response = $client->get($url);
            $html = (string) $response->getBody();

            $crawler = new Crawler($html);

            // Step 1: Find all tables and identify which one belongs to our intake
            $tables = $crawler->filter('table');
            $targetTable = null;
            $programName = '';
            $semesterInfo = '';

            // Look through all tables to find the one for our intake
            foreach ($tables as $table) {
                $tableCrawler = new Crawler($table);

                // Get all text content around this table
                $surroundingText = $this->getSurroundingText($tableCrawler);

                // Check if this table has our intake
                if (str_contains($surroundingText, "Intake: {$intakeName}")) {
                    $targetTable = $tableCrawler;

                    // Extract program and semester info from surrounding text
                    if (preg_match('/Program:\s*([^\n]+)/', $surroundingText, $programMatch)) {
                        $programName = trim($programMatch[1]);
                    }
                    if (preg_match('/Semester:\s*([^\n]+)/', $surroundingText, $semesterMatch)) {
                        $semesterInfo = trim($semesterMatch[1]);
                    }
                    break;
                }
            }

            if (!$targetTable) {
                return response()->json([
                    'error' => 'Routine table not found for this intake on BUBT Website.'
                ], 404);
            }

            // Step 2: Convert HTML table → JSON
            $rows = $targetTable->filter('tr');
            $jsonData = [];

            // Extract time slots from header
            $headerCells = $rows->first()->filter('td, th')->each(function ($th) {
                return trim($th->text());
            });

            // Remove "Day/Time" first column header
            array_shift($headerCells);

            // Define day names that might appear in the table
            $possibleDayNames = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THR', 'FRI'];

            // Function to parse course information from table cell
            $parseCourseInfo = function ($cellText) {
                if (empty(trim($cellText))) {
                    return [];
                }

                $courses = [];
                // Split multiple courses in one cell by new lines first
                $courseBlocks = preg_split('/\n+/', trim($cellText));

                foreach ($courseBlocks as $block) {
                    $block = trim($block);
                    if (empty($block))
                        continue;

                    $courseCode = '';
                    $facultyCode = '';
                    $room = '';

                    // Try different patterns to extract course info
                    $patterns = [
                        // Pattern for: "CSE 319 FC: AHS R: 2320"
                        '/^([A-Z]{2,}\s*\d+[A-Z]*)\s+FC:\s*([A-Za-z]+)\s+R:\s*(\d+)$/',
                        // Pattern for: "CSE 319\nFC: AHS\nR: 2320" (already split by new lines)
                        '/^([A-Z]{2,}\s*\d+[A-Z]*)$/'
                    ];

                    $isCourseCodeLine = preg_match($patterns[1], $block, $matches) || preg_match($patterns[0], $block, $matches);

                    if ($isCourseCodeLine && !empty($matches[1])) {
                        $courseCode = $matches[1];
                        $facultyCode = $matches[2] ?? '';
                        $room = $matches[3] ?? '';
                    } else {
                        // Check if this is a faculty code line
                        if (str_starts_with($block, 'FC:')) {
                            $facultyCode = trim(substr($block, 3));
                        }
                        // Check if this is a room line
                        elseif (str_starts_with($block, 'R:')) {
                            $room = trim(substr($block, 2));
                        }
                        // Otherwise, assume it's a course code
                        else {
                            $courseCode = $block;
                        }
                    }

                    if (!empty($courseCode)) {
                        $courses[] = [
                            'course_code' => $courseCode,
                            'faculty_code' => $facultyCode,
                            'room' => $room,
                        ];
                    }
                }

                return $courses;
            };

            // Process each row (skip header row)
            for ($rowIndex = 1; $rowIndex < $rows->count(); $rowIndex++) {
                $rowNode = $rows->eq($rowIndex);
                $cells = $rowNode->filter('td');

                if ($cells->count() === 0)
                    continue;

                // Get day from first cell
                $day = trim($cells->first()->text());

                // Skip if this doesn't look like a day row
                if (!in_array($day, $possibleDayNames)) {
                    continue;
                }

                $hasClasses = false;
                $timeSlots = [];

                // Process each time slot (skip first cell which is the day)
                for ($i = 1; $i < $cells->count() && ($i - 1) < count($headerCells); $i++) {
                    $cellNode = $cells->eq($i);
                    $timeSlot = $headerCells[$i - 1] ?? "";

                    $cellText = $cellNode->text();

                    if (!empty(trim($cellText))) {
                        $parsedCourses = $parseCourseInfo($cellText);

                        foreach ($parsedCourses as $courseInfo) {
                            if (!empty($courseInfo['course_code'])) {
                                $timeSlots[] = [
                                    'time' => $timeSlot,
                                    'course_code' => $courseInfo['course_code'],
                                    'faculty_code' => $courseInfo['faculty_code'],
                                    'room' => $courseInfo['room']
                                ];
                                $hasClasses = true;
                            }
                        }
                    }
                }

                // Only include days that have at least one class
                if ($hasClasses) {
                    $jsonData[] = [
                        'day' => $day,
                        'classes' => $timeSlots
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'program' => $programName ?: $program,
                'intake' => $intakeName,
                'semester' => $semesterInfo ?: $semester,
                'routine' => $jsonData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching routine',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to get text content surrounding a table
     */
    private function getSurroundingText(Crawler $tableCrawler)
    {
        $text = '';

        // Get previous siblings (text before the table)
        $prevNode = $tableCrawler->getNode(0)->previousSibling;
        while ($prevNode) {
            if ($prevNode->nodeType === XML_TEXT_NODE) {
                $text = trim($prevNode->textContent) . ' ' . $text;
            } elseif ($prevNode->nodeType === XML_ELEMENT_NODE) {
                $elementText = trim((new Crawler($prevNode))->text());
                if (!empty($elementText)) {
                    $text = $elementText . ' ' . $text;
                }
            }
            $prevNode = $prevNode->previousSibling;
        }

        // Get the table's own text (headers, etc.)
        $text .= ' ' . $tableCrawler->text();

        return $text;
    }
}