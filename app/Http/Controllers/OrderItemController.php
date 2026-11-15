<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Display a listing of order items.
     */
    public function index(Request $request)
    {
        $query = OrderItem::with(['order', 'product', 'variant']);

        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('product_variant_id')) {
            $query->where('product_variant_id', $request->product_variant_id);
        }

        $orderItems = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orderItems,
        ]);
    }

    /**
     * Store a newly created order item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'special_instructions' => 'nullable|string',
        ]);

        $validated['subtotal'] = $validated['quantity'] * $validated['unit_price'];

        $orderItem = OrderItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Order item created successfully',
            'data' => $orderItem->load(['order', 'product', 'variant']),
        ], 201);
    }

    /**
     * Display the specified order item.
     */
    public function show($id)
    {
        $orderItem = OrderItem::with(['order', 'product', 'variant'])->find($id);

        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $orderItem,
        ]);
    }

    /**
     * Update the specified order item.
     */
    public function update(Request $request, $id)
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found',
            ], 404);
        }

        $validated = $request->validate([
            'order_id' => 'sometimes|required|exists:orders,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'special_instructions' => 'nullable|string',
        ]);

        $quantity = $validated['quantity'] ?? $orderItem->quantity;
        $unitPrice = $validated['unit_price'] ?? $orderItem->unit_price;
        $validated['subtotal'] = $quantity * $unitPrice;

        $orderItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Order item updated successfully',
            'data' => $orderItem->load(['order', 'product', 'variant']),
        ]);
    }

    /**
     * Remove the specified order item.
     */
    public function destroy($id)
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found',
            ], 404);
        }

        $orderItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order item deleted successfully',
        ]);
    }
}
