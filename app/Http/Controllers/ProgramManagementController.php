<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramManagementController extends Controller
{
    public function index(Request $request)
    {
        $programSearch = trim((string) $request->input('program_search', ''));
        $coordSearch   = trim((string) $request->input('coord_search', ''));
        $statusFilter  = $request->input('status', '');

        $programQuery = Program::with('coordinators');
        if ($programSearch) {
            $programQuery->where(fn($q) => $q->where('name', 'like', "%{$programSearch}%")
                ->orWhere('description', 'like', "%{$programSearch}%"));
        }
        if ($statusFilter) {
            $programQuery->where('status', $statusFilter);
        }
        $programs = $programQuery->orderBy('name')->get();

        $coordQuery = Coordinator::with('programs');
        if ($coordSearch) {
            $coordQuery->where(fn($q) => $q->where('full_name', 'like', "%{$coordSearch}%")
                ->orWhere('position', 'like', "%{$coordSearch}%")
                ->orWhere('email', 'like', "%{$coordSearch}%"));
        }
        $coordinators = $coordQuery->orderBy('full_name')->get();

        $allCoordinators = Coordinator::orderBy('full_name')->get();
        $allPrograms     = Program::where('status', 'Active')->orderBy('name')->get();

        $activeTab = $request->input('tab', 'programs');

        return view('program-management.index', compact(
            'programs', 'coordinators', 'allCoordinators', 'allPrograms',
            'programSearch', 'coordSearch', 'statusFilter', 'activeTab'
        ));
    }

    public function programsStore(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'required|in:Active,Inactive',
            'coordinators'   => 'nullable|array',
            'coordinators.*' => 'exists:coordinators,id',
        ]);

        $program = Program::create($request->only(['name', 'description', 'status']));
        if ($request->filled('coordinators')) {
            $program->coordinators()->sync($request->coordinators);
        }

        return redirect()->route('program-management.index', ['tab' => 'programs'])
            ->with('success', 'Program created successfully.');
    }

    public function programsUpdate(Request $request, Program $program)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'required|in:Active,Inactive',
            'coordinators'   => 'nullable|array',
            'coordinators.*' => 'exists:coordinators,id',
        ]);

        $program->update($request->only(['name', 'description', 'status']));
        $program->coordinators()->sync($request->input('coordinators', []));

        return redirect()->route('program-management.index', ['tab' => 'programs'])
            ->with('success', 'Program updated successfully.');
    }

    public function programsDestroy(Program $program)
    {
        $program->coordinators()->detach();
        $program->delete();

        return redirect()->route('program-management.index', ['tab' => 'programs'])
            ->with('success', 'Program deleted.');
    }

    public function coordinatorsStore(Request $request)
    {
        $request->validate([
            'full_name'      => 'required|string|max:255',
            'position'       => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'programs'       => 'nullable|array',
            'programs.*'     => 'exists:programs,id',
        ]);

        $coordinator = Coordinator::create($request->only(['full_name', 'position', 'contact_number', 'email']));
        if ($request->filled('programs')) {
            $coordinator->programs()->sync($request->programs);
        }

        return redirect()->route('program-management.index', ['tab' => 'coordinators'])
            ->with('success', 'Coordinator created successfully.');
    }

    public function coordinatorsUpdate(Request $request, Coordinator $coordinator)
    {
        $request->validate([
            'full_name'      => 'required|string|max:255',
            'position'       => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'programs'       => 'nullable|array',
            'programs.*'     => 'exists:programs,id',
        ]);

        $coordinator->update($request->only(['full_name', 'position', 'contact_number', 'email']));
        $coordinator->programs()->sync($request->input('programs', []));

        return redirect()->route('program-management.index', ['tab' => 'coordinators'])
            ->with('success', 'Coordinator updated successfully.');
    }

    public function coordinatorsDestroy(Coordinator $coordinator)
    {
        $coordinator->programs()->detach();
        $coordinator->delete();

        return redirect()->route('program-management.index', ['tab' => 'coordinators'])
            ->with('success', 'Coordinator deleted.');
    }
}
