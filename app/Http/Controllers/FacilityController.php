<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $facilities = Facility::all();
                $data = [];
                
                foreach ($facilities as $facility) {
                    $data[] = [
                        'id' => $facility->id,
                        'name' => $facility->name,
                        'category' => Facility::getCategories()[$facility->category] ?? $facility->category,
                        'icon' => $facility->icon ? '<i class="' . $facility->icon . '"></i> ' . $facility->icon : 'No icon',
                        'is_active' => $facility->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>',
                        'action' => '
                            <a href="' . route('facility.show', $facility->id) . '" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="' . route('facility.edit', $facility->id) . '" class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="' . route('facility.destroy', $facility->id) . '" method="POST" class="d-inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-danger delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>'
                    ];
                }
                
                return response()->json([
                    'data' => $data
                ]);
            } catch (\Exception $e) {
                \Log::error('Facility data error: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Error fetching facility data: ' . $e->getMessage()
                ], 500);
            }
        }
        
        $facilities = Facility::all();
        return view('facility.index', compact('facilities'));
    }

    public function create()
    {
        return view('facility.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'detail' => 'required|string',
            'category' => 'required|in:' . implode(',', array_keys(Facility::getCategories())),
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        Facility::create($validated);

        return redirect()->route('facility.index')
            ->with('success', 'Facility created successfully.');
    }

    public function show(Facility $facility)
    {
        return view('facility.show', compact('facility'));
    }

    public function edit(Facility $facility)
    {
        return view('facility.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'detail' => 'required|string',
            'category' => 'required|in:' . implode(',', array_keys(Facility::getCategories())),
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $facility->update($validated);

        return redirect()->route('facility.index')
            ->with('success', 'Facility updated successfully.');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()->route('facility.index')
            ->with('success', 'Facility deleted successfully.');
    }
}
