<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Program::with('coordinators');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $programs = $query->orderBy('name')->paginate(15);

        return view('programs.index', compact('programs', 'search', 'status'));
    }

    public function create()
    {
        $coordinators = Coordinator::orderBy('full_name')->get();
        return view('programs.create', compact('coordinators'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
            'coordinators' => 'nullable|array',
            'coordinators.*' => 'exists:coordinators,id',
        ]);

        $program = Program::create($request->only(['name', 'description', 'status']));

        if ($request->filled('coordinators')) {
            $program->coordinators()->sync($request->coordinators);
        }

        return redirect()->route('programs.index')->with('success', 'Program created successfully.');
    }

    public function show(Program $program)
    {
        $program->load('coordinators');
        return view('programs.show', compact('program'));
    }

    public function edit(Program $program)
    {
        $program->load('coordinators');
        $coordinators = Coordinator::orderBy('full_name')->get();
        return view('programs.edit', compact('program', 'coordinators'));
    }

    public function update(Request $request, Program $program)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
            'coordinators' => 'nullable|array',
            'coordinators.*' => 'exists:coordinators,id',
        ]);

        $program->update($request->only(['name', 'description', 'status']));

        if ($request->has('coordinators')) {
            $program->coordinators()->sync($request->coordinators ?? []);
        }

        return redirect()->route('programs.index')->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        $program->coordinators()->detach();
        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Program deleted successfully.');
    }
}

