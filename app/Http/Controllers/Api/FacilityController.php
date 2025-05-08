<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    /**
     * Display a listing of the facilities.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $facilities = Facility::all();
        return response()->json([
            'status' => 'success',
            'data' => $facilities
        ]);
    }

    /**
     * Display facilities by category.
     *
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCategory($category)
    {
        $validator = Validator::make(['category' => $category], [
            'category' => ['required', Rule::in(array_keys(Facility::getCategories()))]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid category',
                'errors' => $validator->errors()
            ], 422);
        }

        $facilities = Facility::where('category', $category)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $facilities
        ]);
    }

    /**
     * Store a newly created facility in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'detail' => 'required|string',
            'category' => ['required', Rule::in(array_keys(Facility::getCategories()))],
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $facility = Facility::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Facility created successfully',
            'data' => $facility
        ], 201);
    }

    /**
     * Display the specified facility.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'status' => 'error',
                'message' => 'Facility not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $facility
        ]);
    }

    /**
     * Update the specified facility in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'status' => 'error',
                'message' => 'Facility not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'detail' => 'sometimes|required|string',
            'category' => ['sometimes', 'required', Rule::in(array_keys(Facility::getCategories()))],
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $facility->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Facility updated successfully',
            'data' => $facility
        ]);
    }

    /**
     * Remove the specified facility from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $facility = Facility::find($id);

        if (!$facility) {
            return response()->json([
                'status' => 'error',
                'message' => 'Facility not found'
            ], 404);
        }

        $facility->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Facility deleted successfully'
        ]);
    }
} 