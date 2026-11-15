<?php

namespace App\Http\Controllers;

use App\Models\UnitType;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
    public function index(Request $request)
    {
        $units = UnitType::withCount('variations')->orderBy('name', 'asc')->get();
        return response()->json(['success' => true, 'data' => $units]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $unit = UnitType::create($validated);
        return response()->json(['success' => true, 'message' => 'Unit type created', 'data' => $unit], 201);
    }

    public function show($id)
    {
        $unit = UnitType::with('variations')->find($id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit type not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $unit]);
    }

    public function update(Request $request, $id)
    {
        $unit = UnitType::find($id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit type not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $unit->update($validated);
        return response()->json(['success' => true, 'message' => 'Unit type updated', 'data' => $unit]);
    }

    public function destroy($id)
    {
        $unit = UnitType::find($id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit type not found'], 404);
        }

        $unit->delete();
        return response()->json(['success' => true, 'message' => 'Unit type deleted']);
    }
}
