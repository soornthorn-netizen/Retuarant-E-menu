<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Display a listing of promotions.
     */
    public function index(Request $request)
    {
        $query = Promotion::with(['restaurant', 'products', 'variations']);

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->boolean('active_now')) {
            $now = now();
            $query->where('is_active', true)
                  ->where('start_date', '<=', $now)
                  ->where('end_date', '>=', $now);
        }

        $promotions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $promotions,
        ]);
    }

    /**
     * Store a newly created promotion.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'name' => 'required|string|max:150',
            'discount_type' => 'required|integer',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $promotion = Promotion::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Promotion created successfully',
            'data' => $promotion,
        ], 201);
    }

    /**
     * Display the specified promotion.
     */
    public function show($id)
    {
        $promotion = Promotion::with(['restaurant', 'products', 'variations'])->find($id);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $promotion,
        ]);
    }

    /**
     * Update the specified promotion.
     */
    public function update(Request $request, $id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'name' => 'sometimes|required|string|max:150',
            'discount_type' => 'sometimes|required|integer',
            'discount_value' => 'sometimes|required|numeric|min:0',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $promotion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Promotion updated successfully',
            'data' => $promotion,
        ]);
    }

    /**
     * Remove the specified promotion.
     */
    public function destroy($id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found',
            ], 404);
        }

        $promotion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Promotion deleted successfully',
        ]);
    }
}
