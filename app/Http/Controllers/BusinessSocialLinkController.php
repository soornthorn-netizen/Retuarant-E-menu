<?php

namespace App\Http\Controllers;

use App\Models\BusinessSocialLink;
use Illuminate\Http\Request;

class BusinessSocialLinkController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessSocialLink::with(['business', 'social']);

        if ($request->has('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        if ($request->has('social_id')) {
            $query->where('social_id', $request->social_id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:restaurants,id',
            'social_id' => 'required|exists:socials,id',
            'value' => 'required|string|max:255',
        ]);

        $link = BusinessSocialLink::updateOrCreate(
            ['business_id' => $validated['business_id'], 'social_id' => $validated['social_id']],
            ['value' => $validated['value']]
        );

        return response()->json(['success' => true, 'message' => 'Social link saved', 'data' => $link->load(['business', 'social'])], 201);
    }

    public function show($id)
    {
        $link = BusinessSocialLink::with(['business', 'social'])->find($id);
        if (!$link) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $link]);
    }

    public function update(Request $request, $id)
    {
        $link = BusinessSocialLink::find($id);
        if (!$link) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $link->update($validated);
        return response()->json(['success' => true, 'message' => 'Social link updated', 'data' => $link->load(['business', 'social'])]);
    }

    public function destroy($id)
    {
        $link = BusinessSocialLink::find($id);
        if (!$link) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $link->delete();
        return response()->json(['success' => true, 'message' => 'Social link deleted']);
    }
}
