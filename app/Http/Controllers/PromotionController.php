<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    /**
     * Display a listing of promotions.
     */
    public function index(Request $request)
    {
        $query = Promotion::with('restaurant');

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('active_now')) {
            $now = now();
            $query->where('status', 'active')
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|string|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'coupon_code' => 'nullable|string|max:50|unique:promotions,coupon_code',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'min_order_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive',
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
        $promotion = Promotion::with('restaurant')->find($id);

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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'sometimes|required|string|in:percentage,fixed',
            'discount_value' => 'sometimes|required|numeric|min:0',
            'coupon_code' => ['nullable', 'string', 'max:50', Rule::unique('promotions')->ignore($promotion->id)],
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'min_order_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive',
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

