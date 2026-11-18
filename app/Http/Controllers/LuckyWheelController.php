<?php

namespace App\Http\Controllers;

use App\Models\LuckyWheelPrize;
use App\Models\LuckyWheelSpin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LuckyWheelController extends Controller
{
    /**
     * Get active wheel slices/prizes to render in the frontend lucky wheel.
     */
    public function prizes(Request $request)
    {
        $query = LuckyWheelPrize::with('product')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('remaining_quantity')
                  ->orWhere('remaining_quantity', '>', 0);
            });

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        $prizes = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'count' => $prizes->count(),
            'data' => $prizes,
        ]);
    }

    /**
     * Spin the lucky wheel.
     * Uses a weighted random algorithm to pick a winning slice.
     */
    public function spin(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'branch_id' => 'nullable|exists:branches,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $query = LuckyWheelPrize::with('product')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('remaining_quantity')
                  ->orWhere('remaining_quantity', '>', 0);
            });

        if (!empty($validated['restaurant_id'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('restaurant_id', $validated['restaurant_id'])
                  ->orWhereNull('restaurant_id');
            });
        }

        $prizes = $query->orderBy('id', 'asc')->get();

        if ($prizes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active prizes available on the lucky wheel right now.',
            ], 404);
        }

        // Weighted random selection based on probability
        $totalWeight = $prizes->sum('probability');
        if ($totalWeight <= 0) {
            $totalWeight = $prizes->count();
            $weights = array_fill(0, $prizes->count(), 1);
        } else {
            $weights = $prizes->pluck('probability')->toArray();
        }

        $random = mt_rand(1, $totalWeight);
        $currentWeight = 0;
        $winningIndex = 0;
        $winningPrize = $prizes[0];

        foreach ($prizes as $index => $prize) {
            $currentWeight += ($prize->probability > 0 ? $prize->probability : 1);
            if ($random <= $currentWeight) {
                $winningIndex = $index;
                $winningPrize = $prize;
                break;
            }
        }

        // Decrement remaining quantity if finite
        if ($winningPrize->remaining_quantity !== null && $winningPrize->remaining_quantity > 0) {
            $winningPrize->decrement('remaining_quantity');
        }

        // Generate unique redeem code
        $redeemCode = 'SPIN-' . strtoupper(Str::random(8));

        // Create spin history record
        $userId = auth('sanctum')->id() ?? $request->user()?->id;

        $spin = LuckyWheelSpin::create([
            'user_id' => $userId,
            'customer_name' => $validated['customer_name'] ?? ($request->user()?->name ?? 'Guest'),
            'customer_phone' => $validated['customer_phone'] ?? null,
            'restaurant_id' => $validated['restaurant_id'] ?? $winningPrize->restaurant_id,
            'branch_id' => $validated['branch_id'] ?? null,
            'prize_id' => $winningPrize->id,
            'redeem_code' => $redeemCode,
            'is_redeemed' => false,
            'expires_at' => now()->addDays(7), // Valid for 7 days
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $winningPrize->prize_type === 'try_again'
                ? 'Better luck next time!'
                : 'Congratulations! You won: ' . $winningPrize->title,
            'data' => [
                'spin_id' => $spin->id,
                'redeem_code' => $redeemCode,
                'slice_index' => $winningIndex, // 0-based index for frontend wheel animation
                'total_slices' => $prizes->count(),
                'prize' => $winningPrize,
                'expires_at' => $spin->expires_at,
            ],
        ], 200);
    }

    /**
     * Customer won prizes / spin history.
     */
    public function history(Request $request)
    {
        $query = LuckyWheelSpin::with(['prize.product', 'restaurant', 'branch']);

        if ($request->has('customer_phone')) {
            $query->where('customer_phone', $request->customer_phone);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif ($userId = auth('sanctum')->id()) {
            $query->where('user_id', $userId);
        }

        if ($request->has('is_redeemed')) {
            $query->where('is_redeemed', filter_var($request->is_redeemed, FILTER_VALIDATE_BOOLEAN));
        }

        $spins = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $spins,
        ]);
    }

    /**
     * Verify or redeem a won prize at the restaurant using its redeem code.
     * Supports:
     * - GET  /api/lucky-wheel/redeem?redeem_code=SPIN-XXXXX (Inspect/verify code without consuming)
     * - POST /api/lucky-wheel/redeem {"redeem_code": "SPIN-XXXXX"} (Consume and redeem code)
     */
    public function redeem(Request $request)
    {
        $code = $request->input('redeem_code') ?? $request->query('redeem_code') ?? $request->query('code');

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'The redeem_code field is required. Provide it in JSON body or as ?redeem_code= query parameter.',
            ], 422);
        }

        $spin = LuckyWheelSpin::with(['prize.product', 'restaurant', 'branch'])
            ->where('redeem_code', strtoupper(trim($code)))
            ->first();

        if (!$spin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid redeem code: ' . $code . '. Please make sure you enter a valid code generated from /api/lucky-wheel/spin.',
            ], 404);
        }

        // If GET request, return code status/preview without consuming it
        if ($request->isMethod('get')) {
            $isExpired = $spin->expires_at && now()->isAfter($spin->expires_at);
            return response()->json([
                'success' => true,
                'message' => 'Redeem code verified successfully.',
                'can_redeem' => !$spin->is_redeemed && !$isExpired,
                'is_redeemed' => (bool)$spin->is_redeemed,
                'is_expired' => (bool)$isExpired,
                'data' => $spin,
            ]);
        }

        // POST request: perform redemption
        if ($spin->is_redeemed) {
            return response()->json([
                'success' => false,
                'message' => 'This prize has already been redeemed on ' . $spin->redeemed_at?->format('Y-m-d H:i') . '.',
            ], 400);
        }

        if ($spin->expires_at && now()->isAfter($spin->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'This prize has expired on ' . $spin->expires_at->format('Y-m-d H:i') . '.',
            ], 400);
        }

        $spin->update([
            'is_redeemed' => true,
            'redeemed_at' => now(),
            'branch_id' => $request->input('branch_id') ?? $spin->branch_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prize redeemed successfully!',
            'data' => $spin,
        ]);
    }

    /**
     * Direct URL endpoint to verify a code: GET /api/lucky-wheel/verify/{code}
     */
    public function verifyCode(string $code)
    {
        $spin = LuckyWheelSpin::with(['prize.product', 'restaurant', 'branch'])
            ->where('redeem_code', strtoupper(trim($code)))
            ->first();

        if (!$spin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid redeem code: ' . $code,
            ], 404);
        }

        $isExpired = $spin->expires_at && now()->isAfter($spin->expires_at);

        return response()->json([
            'success' => true,
            'message' => 'Redeem code verified successfully.',
            'can_redeem' => !$spin->is_redeemed && !$isExpired,
            'is_redeemed' => (bool)$spin->is_redeemed,
            'is_expired' => (bool)$isExpired,
            'data' => $spin,
        ]);
    }
}

