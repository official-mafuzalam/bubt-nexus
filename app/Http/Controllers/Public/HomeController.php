<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('public/Welcome');
    }

    public function coverPageGenerator()
    {
        // Available templates
        $templates = [
            ['id' => 1, 'name' => 'Modern Blue', 'preview' => '/images/templates/modern-blue.jpg'],
            ['id' => 2, 'name' => 'Classic Green', 'preview' => '/images/templates/classic-green.jpg'],
            ['id' => 3, 'name' => 'Professional Gray', 'preview' => '/images/templates/professional-gray.jpg'],
            ['id' => 4, 'name' => 'Academic Red', 'preview' => '/images/templates/academic-red.jpg'],
        ];

        return Inertia::render('public/CoverPageGenerator', [
            'templates' => $templates
        ]);
    }

    public function previewCoverPage(Request $request)
    {
        try {
            $request->validate([
                'template_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'id' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'session' => 'required|string|max:255',
            ]);

            $data = [
                'name' => $request->input('name'),
                'id' => $request->input('id'),
                'department' => $request->input('department'),
                'semester' => $request->input('semester'),
                'session' => $request->input('session'),
                'subject' => $request->input('subject'),
                'title' => $request->input('title'),
                'template_id' => $request->input('template_id'),
                'date' => now()->format('F d, Y'),
            ];

            // Load the view and generate PDF
            $pdf = Pdf::loadView('cover-pages.template-' . $request->input('template_id'), $data);

            // Return PDF with proper headers for preview
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="preview.pdf"')
                ->header('Cache-Control', 'private, max-age=0, must-revalidate')
                ->header('Pragma', 'public');

        } catch (\Exception $e) {
            Log::error('Preview Cover Page Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to generate preview',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cgpaCalculator()
    {
        return Inertia::render('public/CgpaCalculator');
    }

    public function notes()
    {
        return Inertia::render('public/Notes');
    }

}
