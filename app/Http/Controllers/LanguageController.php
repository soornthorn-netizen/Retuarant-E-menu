<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $languages = Language::orderBy('name', 'asc')->get();
        return response()->json(['success' => true, 'data' => $languages]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:languages,code',
        ]);

        $language = Language::create($validated);
        return response()->json(['success' => true, 'message' => 'Language created successfully', 'data' => $language], 201);
    }

    public function show($id)
    {
        $language = Language::with(['categoryLanguages', 'productLanguages'])->find($id);
        if (!$language) {
            return response()->json(['success' => false, 'message' => 'Language not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $language]);
    }

    public function update(Request $request, $id)
    {
        $language = Language::find($id);
        if (!$language) {
            return response()->json(['success' => false, 'message' => 'Language not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'code' => 'sometimes|required|string|max:10|unique:languages,code,' . $id,
        ]);

        $language->update($validated);
        return response()->json(['success' => true, 'message' => 'Language updated successfully', 'data' => $language]);
    }

    public function destroy($id)
    {
        $language = Language::find($id);
        if (!$language) {
            return response()->json(['success' => false, 'message' => 'Language not found'], 404);
        }

        $language->delete();
        return response()->json(['success' => true, 'message' => 'Language deleted successfully']);
    }
}
