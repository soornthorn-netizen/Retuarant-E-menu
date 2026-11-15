<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of product variants.
     */
    public function index(Request $request)
    {
        $query = ProductVariant::with('product');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', filter_var($request->is_available, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $variants = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $variants,
        ]);
    }

    /**
     * Store a newly created product variant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        $variant = ProductVariant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product variant created successfully',
            'data' => $variant->load('product'),
        ], 201);
    }

    /**
     * Display the specified product variant.
     */
    public function show($id)
    {
        $variant = ProductVariant::with('product')->find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $variant,
        ]);
    }

    /**
     * Update the specified product variant.
     */
    public function update(Request $request, $id)
    {
        $variant = ProductVariant::find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        $variant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product variant updated successfully',
            'data' => $variant->load('product'),
        ]);
    }

    /**
     * Remove the specified product variant.
     */
    public function destroy($id)
    {
        $variant = ProductVariant::find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        $variant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product variant deleted successfully',
        ]);
    }
}

