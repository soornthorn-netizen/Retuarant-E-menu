<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    /**
     * Display a listing of menu items.
     */
    public function index(Request $request)
    {
        $query = MenuItem::with(['menu', 'product']);

        if ($request->has('menu_id')) {
            $query->where('menu_id', $request->menu_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', filter_var($request->is_available, FILTER_VALIDATE_BOOLEAN));
        }

        $menuItems = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $menuItems,
        ]);
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'product_id' => 'required|exists:products,id',
            'custom_price' => 'nullable|numeric|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        $menuItem = MenuItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully',
            'data' => $menuItem->load(['menu', 'product']),
        ], 201);
    }

    /**
     * Display the specified menu item.
     */
    public function show($id)
    {
        $menuItem = MenuItem::with(['menu', 'product'])->find($id);

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $menuItem,
        ]);
    }

    /**
     * Update the specified menu item.
     */
    public function update(Request $request, $id)
    {
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found',
            ], 404);
        }

        $validated = $request->validate([
            'menu_id' => 'sometimes|required|exists:menus,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'custom_price' => 'nullable|numeric|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        $menuItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully',
            'data' => $menuItem->load(['menu', 'product']),
        ]);
    }

    /**
     * Remove the specified menu item.
     */
    public function destroy($id)
    {
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found',
            ], 404);
        }

        $menuItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully',
        ]);
    }
}

