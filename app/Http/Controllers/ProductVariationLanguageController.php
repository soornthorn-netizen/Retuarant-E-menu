<?php

namespace App\Http\Controllers;

use App\Models\ProductVariationLanguage;
use Illuminate\Http\Request;

class ProductVariationLanguageController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductVariationLanguage::with(['variation', 'language']);

        if ($request->has('product_variation_id')) {
            $query->where('product_variation_id', $request->product_variation_id);
        }

        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variation_id' => 'required|exists:product_variations,id',
            'language_id' => 'required|exists:languages,id',
            'name' => 'required|string|max:255',
        ]);

        $item = ProductVariationLanguage::updateOrCreate(
            ['product_variation_id' => $validated['product_variation_id'], 'language_id' => $validated['language_id']],
            ['name' => $validated['name']]
        );

        return response()->json(['success' => true, 'message' => 'Variation translation saved', 'data' => $item], 201);
    }

    public function show($id)
    {
        $item = ProductVariationLanguage::with(['variation', 'language'])->find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = ProductVariationLanguage::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item->update($validated);
        return response()->json(['success' => true, 'message' => 'Updated successfully', 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = ProductVariationLanguage::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $item->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}
