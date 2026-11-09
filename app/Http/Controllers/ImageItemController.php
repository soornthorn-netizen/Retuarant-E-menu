<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImageItem;
use Illuminate\Http\Request;

class ImageItemController extends Controller
{
    /**
     * Display a listing of image items.
     */
    public function index(Request $request)
    {
        $query = ImageItem::with('product');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('is_primary')) {
            $query->where('is_primary', filter_var($request->is_primary, FILTER_VALIDATE_BOOLEAN));
        }

        $imageItems = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $imageItems,
        ]);
    }

    /**
     * Store a newly created image item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'image_url' => 'required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        $imageItem = ImageItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image item created successfully',
            'data' => $imageItem->load('product'),
        ], 201);
    }

    /**
     * Display the specified image item.
     */
    public function show($id)
    {
        $imageItem = ImageItem::with('product')->find($id);

        if (!$imageItem) {
            return response()->json([
                'success' => false,
                'message' => 'Image item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $imageItem,
        ]);
    }

    /**
     * Update the specified image item.
     */
    public function update(Request $request, $id)
    {
        $imageItem = ImageItem::find($id);

        if (!$imageItem) {
            return response()->json([
                'success' => false,
                'message' => 'Image item not found',
            ], 404);
        }

        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'image_url' => 'sometimes|required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        $imageItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image item updated successfully',
            'data' => $imageItem->load('product'),
        ]);
    }

    /**
     * Remove the specified image item.
     */
    public function destroy($id)
    {
        $imageItem = ImageItem::find($id);

        if (!$imageItem) {
            return response()->json([
                'success' => false,
                'message' => 'Image item not found',
            ], 404);
        }

        $imageItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image item deleted successfully',
        ]);
    }
}

