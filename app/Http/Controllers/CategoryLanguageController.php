<?php

namespace App\Http\Controllers;

use App\Models\CategoryLanguage;
use Illuminate\Http\Request;

class CategoryLanguageController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoryLanguage::with(['category', 'language']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'language_id' => 'required|exists:languages,id',
            'name' => 'required|string|max:150',
        ]);

        $item = CategoryLanguage::updateOrCreate(
            ['category_id' => $validated['category_id'], 'language_id' => $validated['language_id']],
            ['name' => $validated['name']]
        );

        return response()->json(['success' => true, 'message' => 'Category translation saved', 'data' => $item->load(['category', 'language'])], 201);
    }

    public function show($id)
    {
        $item = CategoryLanguage::with(['category', 'language'])->find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = CategoryLanguage::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
        ]);

        $item->update($validated);
        return response()->json(['success' => true, 'message' => 'Updated successfully', 'data' => $item->load(['category', 'language'])]);
    }

    public function destroy($id)
    {
        $item = CategoryLanguage::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}

