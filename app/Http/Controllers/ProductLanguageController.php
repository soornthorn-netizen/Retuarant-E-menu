<?php

namespace App\Http\Controllers;

use App\Models\ProductLanguage;
use Illuminate\Http\Request;

class ProductLanguageController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductLanguage::with(['product', 'language']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'language_id' => 'required|exists:languages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $item = ProductLanguage::updateOrCreate(
            ['product_id' => $validated['product_id'], 'language_id' => $validated['language_id']],
            ['name' => $validated['name'], 'description' => $validated['description'] ?? null]
        );

        return response()->json(['success' => true, 'message' => 'Product translation saved', 'data' => $item], 201);
    }

    public function show($id)
    {
        $item = ProductLanguage::with(['product', 'language'])->find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = ProductLanguage::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $item->update($validated);
        return response()->json(['success' => true, 'message' => 'Updated successfully', 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = ProductLanguage::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}
