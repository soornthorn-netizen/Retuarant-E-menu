<?php

namespace App\Http\Controllers;

use App\Models\LuckyWheelPrize;
use Illuminate\Http\Request;

class LuckyWheelPrizeController extends Controller
{
    /**
     * Display a listing of lucky wheel prizes.
     */
    public function index(Request $request)
    {
        $query = LuckyWheelPrize::with(['restaurant', 'product']);

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('prize_type')) {
            $query->where('prize_type', $request->prize_type);
        }

        $prizes = $query->orderBy('probability', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $prizes,
        ]);
    }

    /**
     * Store a newly created lucky wheel prize.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'prize_type' => 'required|in:free_product,discount_percentage,discount_fixed,coupon,try_again',
            'discount_value' => 'nullable|numeric|min:0',
            'product_id' => 'nullable|exists:products,id',
            'coupon_code' => 'nullable|string|max:50',
            'probability' => 'required|integer|min:0',
            'slice_color' => 'nullable|string|max:30',
            'text_color' => 'nullable|string|max:30',
            'icon' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'remaining_quantity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['quantity']) && !isset($validated['remaining_quantity'])) {
            $validated['remaining_quantity'] = $validated['quantity'];
        }

        $prize = LuckyWheelPrize::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lucky wheel prize created successfully',
            'data' => $prize->load(['restaurant', 'product']),
        ], 201);
    }

    /**
     * Display the specified lucky wheel prize.
     */
    public function show($id)
    {
        $prize = LuckyWheelPrize::with(['restaurant', 'product', 'spins'])->find($id);

        if (!$prize) {
            return response()->json([
                'success' => false,
                'message' => 'Prize not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $prize,
        ]);
    }

    /**
     * Update the specified lucky wheel prize.
     */
    public function update(Request $request, $id)
    {
        $prize = LuckyWheelPrize::find($id);

        if (!$prize) {
            return response()->json([
                'success' => false,
                'message' => 'Prize not found',
            ], 404);
        }

        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'title' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'prize_type' => 'sometimes|required|in:free_product,discount_percentage,discount_fixed,coupon,try_again',
            'discount_value' => 'nullable|numeric|min:0',
            'product_id' => 'nullable|exists:products,id',
            'coupon_code' => 'nullable|string|max:50',
            'probability' => 'sometimes|required|integer|min:0',
            'slice_color' => 'nullable|string|max:30',
            'text_color' => 'nullable|string|max:30',
            'icon' => 'nullable|string',
            'quantity' => 'nullable|integer|min:0',
            'remaining_quantity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $prize->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lucky wheel prize updated successfully',
            'data' => $prize->load(['restaurant', 'product']),
        ]);
    }

    /**
     * Remove the specified lucky wheel prize.
     */
    public function destroy($id)
    {
        $prize = LuckyWheelPrize::find($id);

        if (!$prize) {
            return response()->json([
                'success' => false,
                'message' => 'Prize not found',
            ], 404);
        }

        $prize->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lucky wheel prize deleted successfully',
        ]);
    }
}

