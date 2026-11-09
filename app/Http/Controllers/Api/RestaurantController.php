<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Display a listing of restaurants.
     */
    public function index(Request $request)
    {
        $query = Restaurant::with(['branches', 'categories']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $restaurants = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $restaurants,
        ]);
    }

    /**
     * Store a newly created restaurant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $restaurant = Restaurant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant created successfully',
            'data' => $restaurant,
        ], 201);
    }

    /**
     * Display the specified restaurant.
     */
    public function show($id)
    {
        $restaurant = Restaurant::with(['branches', 'categories', 'menus', 'promotions', 'reviews'])->find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $restaurant,
        ]);
    }

    /**
     * Update the specified restaurant.
     */
    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $restaurant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant updated successfully',
            'data' => $restaurant,
        ]);
    }

    /**
     * Remove the specified restaurant.
     */
    public function destroy($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found',
            ], 404);
        }

        $restaurant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurant deleted successfully',
        ]);
    }
}

