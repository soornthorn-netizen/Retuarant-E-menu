<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'promotion',
            'images',
            'languages.language',
            'variations.unitType',
            'options',
        ]);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('tag')) {
            $query->where('tag', $request->tag);
        }

        if ($request->has('promotion_id')) {
            $query->where('promotion_id', $request->promotion_id);
        }

        if ($request->has('manage_stock')) {
            $query->where('manage_stock', filter_var($request->manage_stock, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('tag', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created product with variations, options, images, and translations.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'tag' => 'nullable|string|max:100',
            'manage_stock' => 'nullable|boolean',
            'allow_stock_negative' => 'nullable|boolean',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string|max:255',
            'promotion_id' => 'nullable|exists:promotions,id',
            'images' => 'nullable|array',
            'images.*.image' => 'required_with:images|string|max:255',
            'images.*.sort_order' => 'nullable|integer',
            'variations' => 'nullable|array',
            'variations.*.name' => 'required_with:variations|string|max:255',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.unit_type_id' => 'nullable|exists:unit_types,id',
            'variations.*.variant_group' => 'nullable|string|max:100',
            'variations.*.tag' => 'nullable|string|max:100',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:150',
            'languages' => 'nullable|array',
            'languages.*.language_id' => 'required_with:languages|exists:languages,id',
            'languages.*.name' => 'required_with:languages|string|max:255',
            'languages.*.description' => 'nullable|string',
        ]);

        $images = $validated['images'] ?? [];
        $variations = $validated['variations'] ?? [];
        $options = $validated['options'] ?? [];
        $languages = $validated['languages'] ?? [];
        unset($validated['images'], $validated['variations'], $validated['options'], $validated['languages']);

        $product = Product::create($validated);

        if (!empty($images)) {
            foreach ($images as $img) {
                $product->images()->create($img);
            }
        }

        if (!empty($variations)) {
            foreach ($variations as $var) {
                $product->variations()->create($var);
            }
        }

        if (!empty($options)) {
            foreach ($options as $opt) {
                $product->options()->create([
                    'name' => $opt['name'],
                    'created_at' => now(),
                ]);
            }
        }

        if (!empty($languages)) {
            foreach ($languages as $lang) {
                $product->languages()->create($lang);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['category', 'promotion', 'images', 'languages.language', 'variations.unitType', 'options']),
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::with([
            'category',
            'promotion',
            'images',
            'languages.language',
            'variations.unitType',
            'variations.languages.language',
            'options',
            'menuItems',
            'reviews',
        ])->find($id);

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
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'tag' => 'nullable|string|max:100',
            'manage_stock' => 'nullable|boolean',
            'allow_stock_negative' => 'nullable|boolean',
            'category_id' => 'sometimes|required|exists:categories,id',
            'image' => 'nullable|string|max:255',
            'promotion_id' => 'nullable|exists:promotions,id',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load(['category', 'promotion', 'images', 'variations', 'options']),
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
