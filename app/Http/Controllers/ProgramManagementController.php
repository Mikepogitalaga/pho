<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramManagementController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('coordinators')->orderBy('name')->paginate(5);
        $coordinators = Coordinator::withCount('programs')->orderBy('full_name')->paginate(5);
        
        return view('program-management.index', compact('programs', 'coordinators'));
    }

    // Programs section
    public function programsIndex(Request $request)
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

        return view('program-management.programs.index', compact('programs', 'search', 'status'));
    }

    public function programsCreate()
    {
        $coordinators = Coordinator::orderBy('full_name')->get();
        return view('program-management.programs.create', compact('coordinators'));
    }

    public function programsStore(Request $request)
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

        return redirect()->route('program-management.programs.index')->with('success', 'Program created successfully.');
    }

    public function programsShow(Program $program)
    {
        $program->load('coordinators');
        return view('program-management.programs.show', compact('program'));
    }

    public function programsEdit(Program $program)
    {
        $program->load('coordinators');
        $coordinators = Coordinator::orderBy('full_name')->get();
        return view('program-management.programs.edit', compact('program', 'coordinators'));
    }

    public function programsUpdate(Request $request, Program $program)
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

        return redirect()->route('program-management.programs.index')->with('success', 'Program updated successfully.');
    }

    public function programsDestroy(Program $program)
    {
        $program->coordinators()->detach();
        $program->delete();

        return redirect()->route('program-management.programs.index')->with('success', 'Program deleted successfully.');
    }

    // Coordinators section
    public function coordinatorsIndex(Request $request)
    {
        $search = $request->query('search');

        $query = Coordinator::with('programs');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $coordinators = $query->orderBy('full_name')->paginate(15);

        return view('program-management.coordinators.index', compact('coordinators', 'search'));
    }

    public function coordinatorsCreate()
    {
        $programs = Program::where('status', 'Active')->orderBy('name')->get();
        return view('program-management.coordinators.create', compact('programs'));
    }

    public function coordinatorsStore(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'programs' => 'nullable|array',
            'programs.*' => 'exists:programs,id',
        ]);

        $coordinator = Coordinator::create($request->only(['full_name', 'position', 'contact_number', 'email']));

        if ($request->filled('programs')) {
            $coordinator->programs()->sync($request->programs);
        }

        return redirect()->route('program-management.coordinators.index')->with('success', 'Coordinator created successfully.');
    }

    public function coordinatorsShow(Coordinator $coordinator)
    {
        $coordinator->load('programs');
        return view('program-management.coordinators.show', compact('coordinator'));
    }

    public function coordinatorsEdit(Coordinator $coordinator)
    {
        $coordinator->load('programs');
        $programs = Program::where('status', 'Active')->orderBy('name')->get();
        return view('program-management.coordinators.edit', compact('coordinator', 'programs'));
    }

    public function coordinatorsUpdate(Request $request, Coordinator $coordinator)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'programs' => 'nullable|array',
            'programs.*' => 'exists:programs,id',
        ]);

        $coordinator->update($request->only(['full_name', 'position', 'contact_number', 'email']));

        if ($request->has('programs')) {
            $coordinator->programs()->sync($request->programs ?? []);
        }

        return redirect()->route('program-management.coordinators.index')->with('success', 'Coordinator updated successfully.');
    }

    public function coordinatorsDestroy(Coordinator $coordinator)
    {
        $coordinator->programs()->detach();
        $coordinator->delete();

        return redirect()->route('program-management.coordinators.index')->with('success', 'Coordinator deleted successfully.');
    }
}
