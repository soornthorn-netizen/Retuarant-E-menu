<?php

namespace App\Http\Controllers;

use App\Models\Social;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Social::withCount('businessLinks')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'image' => 'nullable|string|max:255',
        ]);

        $social = Social::create($validated);
        return response()->json(['success' => true, 'message' => 'Social platform created', 'data' => $social], 201);
    }

    public function show($id)
    {
        $social = Social::with('businessLinks.business')->find($id);
        if (!$social) {
            return response()->json(['success' => false, 'message' => 'Social not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $social]);
    }

    public function update(Request $request, $id)
    {
        $social = Social::find($id);
        if (!$social) {
            return response()->json(['success' => false, 'message' => 'Social not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'image' => 'nullable|string|max:255',
        ]);

        $social->update($validated);
        return response()->json(['success' => true, 'message' => 'Social platform updated', 'data' => $social]);
    }

    public function destroy($id)
    {
        $social = Social::find($id);
        if (!$social) {
            return response()->json(['success' => false, 'message' => 'Social not found'], 404);
        }

        $social->delete();
        return response()->json(['success' => true, 'message' => 'Social platform deleted']);
    }
}
