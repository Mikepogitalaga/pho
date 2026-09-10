<?php

namespace App\Http\Controllers;

use App\Models\Coordinator;
use App\Models\Program;
use Illuminate\Http\Request;

class CoordinatorController extends Controller
{
    public function index(Request $request)
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

        $perPage = (int) $request->query('per_page', 15);

        if ($perPage <= 0) {
            $perPage = PHP_INT_MAX;
        }

        $coordinators = $query->orderBy('full_name')->paginate($perPage)->withQueryString();

        return view('coordinators.index', compact('coordinators', 'search'));
    }

    public function create()
    {
        $programs = Program::where('status', 'Active')->orderBy('name')->get();
        return view('coordinators.create', compact('programs'));
    }

    public function store(Request $request)
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

        return redirect()->route('coordinators.index')->with('success', 'Coordinator created successfully.');
    }

    public function show(Coordinator $coordinator)
    {
        $coordinator->load('programs');
        return view('coordinators.show', compact('coordinator'));
    }

    public function edit(Coordinator $coordinator)
    {
        $coordinator->load('programs');
        $programs = Program::where('status', 'Active')->orderBy('name')->get();
        return view('coordinators.edit', compact('coordinator', 'programs'));
    }

    public function update(Request $request, Coordinator $coordinator)
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

        return redirect()->route('coordinators.index')->with('success', 'Coordinator updated successfully.');
    }

    public function destroy(Coordinator $coordinator)
    {
        $coordinator->programs()->detach();
        $coordinator->delete();

        return redirect()->route('coordinators.index')->with('success', 'Coordinator deleted successfully.');
    }
}

