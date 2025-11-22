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

            $foundIntake = false;
            $routineTable = null;
            $programName = '';
            $semesterInfo = '';
            $currentIntake = '';

            // Step 1: Extract program name, semester info, and find the intake table
            $crawler->filter('*')->each(function ($node) use ($intakeName, &$foundIntake, &$routineTable, &$programName, &$semesterInfo, &$currentIntake) {
                $text = trim($node->text());

                // Check if this node contains intake information
                if (str_contains($text, 'Intake:')) {
                    $currentIntake = trim(str_replace('Intake:', '', $text));

                    // If this is our target intake, set flags and extract info
                    if (trim($currentIntake) === $intakeName) {
                        $foundIntake = true;

                        // Extract program name from previous nodes
                        $prevNode = $node->previousAll()->first();
                        while ($prevNode->count() > 0) {
                            $prevText = trim($prevNode->text());
                            if (str_contains($prevText, 'Program:')) {
                                $programName = trim(str_replace('Program:', '', $prevText));
                                break;
                            }
                            $prevNode = $prevNode->previousAll()->first();
                        }

                        // Extract semester info from next nodes
                        $nextNode = $node->nextAll()->first();
                        while ($nextNode->count() > 0) {
                            $nextText = trim($nextNode->text());
                            if (str_contains($nextText, 'Semester:')) {
                                $semesterInfo = trim(str_replace('Semester:', '', $nextText));
                                break;
                            }
                            $nextNode = $nextNode->nextAll()->first();
                        }
                    }
                }

                // Look for routine table after finding our intake
                if ($foundIntake && $node->nodeName() === 'table') {
                    $headerText = trim($node->filter('tr')->first()->text());

                    if (str_contains($headerText, 'Day/Time')) {
                        $routineTable = $node;
                        $foundIntake = false; // Stop after finding the table
                    }
                }
            });

            if (!$routineTable) {
                return response()->json([
                    'error' => 'Routine table not found for this intake on BUBT Website.'
                ], 404);
            }

            // Step 2: Convert HTML table → JSON
            $rows = $routineTable->filter('tr');
            $jsonData = [];

            $headerCells = $rows->first()->filter('td,th')->each(function ($th) {
                return trim($th->text());
            });

            // Remove "Day/Time" first column header
            array_shift($headerCells);

            // Define day names in table order (SAT, SUN, MON, TUE, WED, THR, FRI)
            $tableDayNames = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THR', 'FRI'];

            // Loop rows
            $rows->each(function ($rowNode, $rowIndex) use (&$jsonData, $headerCells, $tableDayNames) {
                if ($rowIndex === 0)
                    return; // skip header row

                $cells = $rowNode->filter('td')->each(function ($td) {
                    return trim(preg_replace('/\s+/', ' ', $td->text()));
                });

                if (count($cells) === 0)
                    return;

                $dayIndex = $rowIndex - 1;
                $day = $tableDayNames[$dayIndex] ?? "DAY_$dayIndex";

                $dayData = [
                    'day' => $day,
                ];

                $hasClasses = false;
                $timeSlots = [];

                foreach ($headerCells as $i => $timeSlot) {
                    $classInfo = $cells[$i] ?? "";

                    // Only include time slots that have classes (non-empty)
                    if (!empty($classInfo)) {
                        $timeSlots[$timeSlot] = $classInfo;
                        $hasClasses = true;
                    }
                }

                // Only include days that have at least one class
                if ($hasClasses) {
                    $dayData = array_merge(['day' => $day], $timeSlots);
                    $jsonData[] = $dayData;
                }
            });

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
}