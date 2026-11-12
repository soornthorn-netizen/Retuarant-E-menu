<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products with variants.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants', 'imageItems']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('is_available')) {
            $query->where('is_available', filter_var($request->is_available, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created product (and optional variants).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string|max:255',
            'is_available' => 'nullable|boolean',
            'status' => 'nullable|string|in:available,unavailable',
            'is_featured' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.is_available' => 'nullable|boolean',
        ]);

        $variants = $validated['variants'] ?? [];
        unset($validated['variants']);

        $product = Product::create($validated);

        if (!empty($variants)) {
            foreach ($variants as $variant) {
                $product->variants()->create([
                    'name' => $variant['name'],
                    'price' => $variant['price'],
                    'is_available' => $variant['is_available'] ?? true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['category', 'variants', 'imageItems']),
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'variants', 'imageItems', 'menuItems', 'reviews'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string|max:255',
            'is_available' => 'nullable|boolean',
            'status' => 'nullable|string|in:available,unavailable',
            'is_featured' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.is_available' => 'nullable|boolean',
        ]);

        $variants = $validated['variants'] ?? null;
        unset($validated['variants']);

        $product->update($validated);

        // If variants array passed, recreate variants
        if ($variants !== null) {
            $product->variants()->delete();
            foreach ($variants as $variant) {
                $product->variants()->create([
                    'name' => $variant['name'],
                    'price' => $variant['price'],
                    'is_available' => $variant['is_available'] ?? true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load(['category', 'variants', 'imageItems']),
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
