<?php

namespace App\Http\Controllers;

use App\Models\LuckyWheelSpin;
use Illuminate\Http\Request;

class LuckyWheelSpinController extends Controller
{
    /**
     * Display a listing of lucky wheel spins.
     */
    public function index(Request $request)
    {
        $query = LuckyWheelSpin::with(['user', 'restaurant', 'branch', 'prize.product']);

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('is_redeemed')) {
            $query->where('is_redeemed', filter_var($request->is_redeemed, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('redeem_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $spins = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $spins,
        ]);
    }

    /**
     * Display the specified lucky wheel spin.
     */
    public function show($id)
    {
        $spin = LuckyWheelSpin::with(['user', 'restaurant', 'branch', 'prize.product'])->find($id);

        if (!$spin) {
            return response()->json([
                'success' => false,
                'message' => 'Spin record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $spin,
        ]);
    }

    /**
     * Update the specified lucky wheel spin (e.g. mark redeemed manually).
     */
    public function update(Request $request, $id)
    {
        $spin = LuckyWheelSpin::find($id);

        if (!$spin) {
            return response()->json([
                'success' => false,
                'message' => 'Spin record not found',
            ], 404);
        }

        $validated = $request->validate([
            'is_redeemed' => 'nullable|boolean',
            'redeemed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['is_redeemed']) && $validated['is_redeemed'] && empty($spin->redeemed_at)) {
            $validated['redeemed_at'] = now();
        }

        $spin->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Spin record updated successfully',
            'data' => $spin->load(['user', 'restaurant', 'branch', 'prize.product']),
        ]);
    }

    /**
     * Remove the specified lucky wheel spin.
     */
    public function destroy($id)
    {
        $spin = LuckyWheelSpin::find($id);

        if (!$spin) {
            return response()->json([
                'success' => false,
                'message' => 'Spin record not found',
            ], 404);
        }

        $spin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Spin record deleted successfully',
        ]);
    }
}

