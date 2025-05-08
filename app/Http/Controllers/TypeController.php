<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTypeRequest;
use App\Models\Type;
use App\Repositories\Interface\TypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TypeController extends Controller
{
    public function __construct(
        private TypeRepositoryInterface $typeRepository
    ) {
        $this->typeRepository = $typeRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->typeRepository->getTypesDatatable($request);
        }
        return view('type.index');
    }

    public function create()
    {
        return view('type.create');
    }

    public function store(StoreTypeRequest $request)
    {
        try {
            $data = $request->validated();
            
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('room-types', 'public');
            }

            $type = $this->typeRepository->store($data);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Room type has been created successfully!',
                    'type' => $type
                ]);
            }

            return redirect()->route('type.index')->with('success', 'Room type has been created!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error creating room type: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error creating room type: ' . $e->getMessage());
        }
    }

    public function edit(Type $type)
    {
        return view('type.edit', compact('type'));
    }

    public function update(StoreTypeRequest $request, Type $type)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($type->image && Storage::disk('public')->exists($type->image)) {
                Storage::disk('public')->delete($type->image);
            }
            $data['image'] = $request->file('image')->store('room-types', 'public');
        }

        $this->typeRepository->update($data, $type);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Type has been updated successfully!',
                'type' => $type
            ]);
        }

        return redirect()->route('type.index')->with('success', 'Type has been updated!');
    }

    public function destroy(Type $type)
    {
        try {
            // Check if type is being used by any rooms
            if ($type->rooms()->exists()) {
                throw new \Exception('Cannot delete room type that is being used by rooms');
            }

            // Delete image if exists
            if ($type->image && Storage::disk('public')->exists($type->image)) {
                Storage::disk('public')->delete($type->image);
            }

            $this->typeRepository->delete($type);

            if (request()->ajax()) {
                return response()->json([
                    'message' => 'Room type has been deleted successfully!'
                ]);
            }

            return redirect()->route('type.index')->with('success', 'Room type has been deleted!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'message' => 'Error deleting room type: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error deleting room type: ' . $e->getMessage());
        }
    }

    public function getTypes(Request $request)
    {
        return $this->typeRepository->getTypesDatatable($request);
    }
}
