<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoadMap;
use Illuminate\Http\Request;

class RoadMapController extends Controller
{
    /**
     * Display a listing of road maps.
     */
    public function index(Request $request)
    {
        $query = RoadMap::query();

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $roadMaps = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $roadMaps,
        ]);
    }

    /**
     * Store a newly created road map.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $roadMap = RoadMap::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Road map created successfully',
            'data' => $roadMap,
        ], 201);
    }

    /**
     * Display the specified road map.
     */
    public function show($id)
    {
        $roadMap = RoadMap::find($id);

        if (!$roadMap) {
            return response()->json([
                'success' => false,
                'message' => 'Road map not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $roadMap,
        ]);
    }

    /**
     * Update the specified road map.
     */
    public function update(Request $request, $id)
    {
        $roadMap = RoadMap::find($id);

        if (!$roadMap) {
            return response()->json([
                'success' => false,
                'message' => 'Road map not found',
            ], 404);
        }

        $validated = $request->validate([
            'image' => 'sometimes|required|string',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $roadMap->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Road map updated successfully',
            'data' => $roadMap,
        ]);
    }

    /**
     * Remove the specified road map.
     */
    public function destroy($id)
    {
        $roadMap = RoadMap::find($id);

        if (!$roadMap) {
            return response()->json([
                'success' => false,
                'message' => 'Road map not found',
            ], 404);
        }

        $roadMap->delete();

        return response()->json([
            'success' => true,
            'message' => 'Road map deleted successfully',
        ]);
    }
}

