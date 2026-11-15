<?php

namespace App\Http\Controllers;

use App\Models\ProductVariation;
use Illuminate\Http\Request;

class ProductVariationController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductVariation::with(['product', 'unitType', 'promotion', 'languages.language']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('variant_group')) {
            $query->where('variant_group', $request->variant_group);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'product_unit_id' => 'nullable|integer',
            'unit_type_id' => 'nullable|exists:unit_types,id',
            'is_multiplier' => 'nullable|boolean',
            'variant_group' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:100',
            'promotion_id' => 'nullable|exists:promotions,id',
        ]);

        $variation = ProductVariation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product variation created successfully',
            'data' => $variation->load(['product', 'unitType', 'promotion']),
        ], 201);
    }

    public function show($id)
    {
        $variation = ProductVariation::with(['product', 'unitType', 'promotion', 'languages.language'])->find($id);

        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Variation not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $variation]);
    }

    public function update(Request $request, $id)
    {
        $variation = ProductVariation::find($id);

        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Variation not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'product_unit_id' => 'nullable|integer',
            'unit_type_id' => 'nullable|exists:unit_types,id',
            'is_multiplier' => 'nullable|boolean',
            'variant_group' => 'nullable|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|string|max:255',
            'tag' => 'nullable|string|max:100',
            'promotion_id' => 'nullable|exists:promotions,id',
        ]);

        $variation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product variation updated successfully',
            'data' => $variation->load(['product', 'unitType', 'promotion']),
        ]);
    }

    public function destroy($id)
    {
        $variation = ProductVariation::find($id);

        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Variation not found'], 404);
        }

        $variation->delete();

        return response()->json(['success' => true, 'message' => 'Variation deleted successfully']);
    }
}
