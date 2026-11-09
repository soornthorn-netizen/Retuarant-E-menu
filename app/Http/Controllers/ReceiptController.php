<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReceiptController extends Controller
{
    /**
     * Display a listing of receipts.
     */
    public function index(Request $request)
    {
        $query = Receipt::with(['order.branch', 'order.orderItems.product']);

        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->has('search')) {
            $query->where('receipt_number', 'like', '%' . $request->search . '%');
        }

        if ($request->has('date')) {
            $query->whereDate('issued_at', $request->date);
        }

        $receipts = $query->orderBy('issued_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $receipts,
        ]);
    }

    /**
     * Store a newly created receipt.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id|unique:receipts,order_id',
            'receipt_number' => 'nullable|string|max:100|unique:receipts,receipt_number',
            'total' => 'nullable|numeric|min:0',
            'issued_at' => 'nullable|date',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Auto-generate receipt number if not provided
        if (empty($validated['receipt_number'])) {
            $validated['receipt_number'] = 'RCP-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        }

        // Auto-fill total from order final_amount if not specified
        if (!isset($validated['total'])) {
            $validated['total'] = $order->final_amount;
        }

        // Auto-set issued_at to now if not provided
        if (empty($validated['issued_at'])) {
            $validated['issued_at'] = now();
        }

        $receipt = Receipt::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Receipt generated successfully',
            'data' => $receipt->load(['order.branch', 'order.orderItems.product']),
        ], 201);
    }

    /**
     * Display the specified receipt.
     */
    public function show($id)
    {
        $receipt = Receipt::with(['order.branch', 'order.orderItems.product', 'order.payments'])->find($id);

        if (!$receipt) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $receipt,
        ]);
    }

    /**
     * Update the specified receipt.
     */
    public function update(Request $request, $id)
    {
        $receipt = Receipt::find($id);

        if (!$receipt) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found',
            ], 404);
        }

        $validated = $request->validate([
            'receipt_number' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('receipts')->ignore($receipt->id)],
            'total' => 'sometimes|required|numeric|min:0',
            'issued_at' => 'nullable|date',
        ]);

        $receipt->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Receipt updated successfully',
            'data' => $receipt->load(['order.branch', 'order.orderItems.product']),
        ]);
    }

    /**
     * Remove the specified receipt.
     */
    public function destroy($id)
    {
        $receipt = Receipt::find($id);

        if (!$receipt) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found',
            ], 404);
        }

        $receipt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Receipt deleted successfully',
        ]);
    }
}

