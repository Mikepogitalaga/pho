<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index()
    {
        $categories = Facility::categories();
        $facilities = Facility::orderBy('category')->orderBy('name')->get();
        $groupedFacilities = $facilities->groupBy('category');

        return view('facilities.index', compact('categories', 'facilities', 'groupedFacilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', Facility::categories()),
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
        ]);

        Facility::create($request->only(['name', 'category', 'address', 'contact_person', 'phone_number']));

        return redirect()->route('facilities.index')
            ->with('success', 'Facility added successfully.');
    }

    public function update(Request $request, Facility $facility)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', Facility::categories()),
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
        ]);

        $facility->update($request->only(['name', 'category', 'address', 'contact_person', 'phone_number']));

        return redirect()->route('facilities.index')
            ->with('success', 'Facility updated successfully.');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()->route('facilities.index')
            ->with('success', 'Facility deleted successfully.');
    }
}
