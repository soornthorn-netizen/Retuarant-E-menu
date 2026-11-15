<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductImage::with('product');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('sort_order', 'asc')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'image' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $image = ProductImage::create($validated);
        return response()->json(['success' => true, 'message' => 'Product image added', 'data' => $image], 201);
    }

    public function show($id)
    {
        $image = ProductImage::with('product')->find($id);
        if (!$image) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $image]);
    }

    public function update(Request $request, $id)
    {
        $image = ProductImage::find($id);
        if (!$image) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }

        $validated = $request->validate([
            'image' => 'sometimes|required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $image->update($validated);
        return response()->json(['success' => true, 'message' => 'Image updated', 'data' => $image]);
    }

    public function destroy($id)
    {
        $image = ProductImage::find($id);
        if (!$image) {
            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        }

        $image->delete();
        return response()->json(['success' => true, 'message' => 'Image deleted']);
    }
}
