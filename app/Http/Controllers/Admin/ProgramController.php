<?php

namespace App\Http\Controllers\Admin;

use App\Models\Program;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProgramController extends Controller
{
    public function index()
    {
        // Fix: Execute the query with ->get()
        $programs = Program::withCount('students') // This will add students_count
            ->active()
            ->get()
            ->map(function ($program) {
                return [
                    'id' => $program->id,
                    'code' => $program->code,
                    'name' => $program->name,
                    'description' => $program->description,
                    'is_active' => $program->is_active,
                    'students_count' => $program->students_count ?? 0,
                ];
            });

        return Inertia::render('admin/Programs/Index', [
            'programs' => $programs,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/Programs/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:programs,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Program::create($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program created successfully.');
    }

    public function edit(Program $program)
    {
        return Inertia::render('admin/Programs/Edit', [
            'program' => $program,
        ]);
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:programs,code,' . $program->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $program->update($validated);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program deleted successfully.');
    }
}