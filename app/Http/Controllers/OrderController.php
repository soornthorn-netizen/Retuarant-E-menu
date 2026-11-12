<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['branch', 'orderItems.product', 'orderItems.variant', 'payments', 'receipt']);

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->has('table_number')) {
            $query->where('table_number', $request->table_number);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Store a newly created order.
     * Supports 3 order types:
     * 1. dine_in  (requires/uses table_number)
     * 2. takeaway (uses customer_name, customer_phone)
     * 3. delivery (uses customer_name, customer_phone, delivery_address, delivery_fee)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'order_type' => 'required|string|in:dine_in,takeaway,delivery',
            'table_number' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'delivery_address' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:pending,preparing,served,completed,cancelled',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.special_instructions' => 'nullable|string',
        ]);

        $validated['order_number'] = 'ORD-' . strtoupper(Str::random(8));
        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['delivery_fee'] = $validated['order_type'] === 'delivery'
            ? ($validated['delivery_fee'] ?? 0)
            : 0;

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        // Auto-calculate totals if line items provided and amounts not manually passed
        if (!empty($items)) {
            $total = 0;
            foreach ($items as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }
            $validated['total_amount'] = $total;
            $discount = $validated['discount_amount'] ?? 0;
            $deliveryFee = $validated['delivery_fee'] ?? 0;
            $validated['final_amount'] = max(0, $total + $deliveryFee - $discount);
        } else {
            $total = $validated['total_amount'] ?? 0;
            $discount = $validated['discount_amount'] ?? 0;
            $deliveryFee = $validated['delivery_fee'] ?? 0;
            if (!isset($validated['final_amount'])) {
                $validated['final_amount'] = max(0, $total + $deliveryFee - $discount);
            }
        }

        $order = Order::create($validated);

        if (!empty($items)) {
            foreach ($items as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                    'special_instructions' => $item['special_instructions'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order->load(['branch', 'orderItems.product', 'orderItems.variant']),
        ], 201);
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with(['branch', 'orderItems.product', 'orderItems.variant', 'payments', 'receipt'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $validated = $request->validate([
            'branch_id' => 'sometimes|required|exists:branches,id',
            'order_type' => 'nullable|string|in:dine_in,takeaway,delivery',
            'table_number' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'delivery_address' => 'nullable|string',
            'delivery_fee' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'final_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:pending,preparing,served,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['total_amount']) || isset($validated['discount_amount']) || isset($validated['delivery_fee'])) {
            $total = $validated['total_amount'] ?? $order->total_amount;
            $discount = $validated['discount_amount'] ?? $order->discount_amount;
            $deliveryFee = $validated['delivery_fee'] ?? $order->delivery_fee;
            if (!isset($validated['final_amount'])) {
                $validated['final_amount'] = max(0, $total + $deliveryFee - $discount);
            }
        }

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order->load(['branch', 'orderItems.product', 'orderItems.variant', 'payments', 'receipt']),
        ]);
    }

    /**
     * Remove the specified order.
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully',
        ]);
    }
}
