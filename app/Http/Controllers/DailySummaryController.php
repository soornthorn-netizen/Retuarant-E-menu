<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailySummaryController extends Controller
{
    /**
     * Display a listing of daily summaries.
     */
    public function index(Request $request)
    {
        $query = DailySummary::query();

        if ($request->has('summary_date')) {
            $query->whereDate('summary_date', $request->summary_date);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('summary_date', [$request->start_date, $request->end_date]);
        } elseif ($request->has('start_date')) {
            $query->whereDate('summary_date', '>=', $request->start_date);
        } elseif ($request->has('end_date')) {
            $query->whereDate('summary_date', '<=', $request->end_date);
        }

        $summaries = $query->orderBy('summary_date', 'desc')->paginate($request->get('per_page', 30));

        return response()->json([
            'success' => true,
            'data' => $summaries,
        ]);
    }

    /**
     * Store or record a daily summary manually.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'summary_date' => 'required|date|unique:daily_summaries,summary_date',
            'total_orders' => 'nullable|integer|min:0',
            'total_sales' => 'nullable|numeric|min:0',
            'total_discounts' => 'nullable|numeric|min:0',
            'total_tax' => 'nullable|numeric|min:0',
            'total_expenses' => 'nullable|numeric|min:0',
            'total_customers' => 'nullable|integer|min:0',
            'average_order_value' => 'nullable|numeric|min:0',
        ]);

        $summary = DailySummary::create([
            'summary_date' => $validated['summary_date'],
            'total_orders' => $validated['total_orders'] ?? 0,
            'total_sales' => $validated['total_sales'] ?? 0.00,
            'total_discounts' => $validated['total_discounts'] ?? 0.00,
            'total_tax' => $validated['total_tax'] ?? 0.00,
            'total_expenses' => $validated['total_expenses'] ?? 0.00,
            'total_customers' => $validated['total_customers'] ?? 0,
            'average_order_value' => $validated['average_order_value'] ?? 0.00,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily summary created successfully.',
            'data' => $summary,
        ], 201);
    }

    /**
     * Display the specified daily summary.
     */
    public function show(string $id)
    {
        // Allow lookup by ID or by date (YYYY-MM-DD)
        $summary = is_numeric($id) 
            ? DailySummary::find($id) 
            : DailySummary::whereDate('summary_date', $id)->first();

        if (!$summary) {
            return response()->json([
                'success' => false,
                'message' => 'Daily summary not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Update the specified daily summary.
     */
    public function update(Request $request, string $id)
    {
        $summary = is_numeric($id) 
            ? DailySummary::find($id) 
            : DailySummary::whereDate('summary_date', $id)->first();

        if (!$summary) {
            return response()->json([
                'success' => false,
                'message' => 'Daily summary not found.',
            ], 404);
        }

        $validated = $request->validate([
            'summary_date' => 'sometimes|required|date|unique:daily_summaries,summary_date,' . $summary->id,
            'total_orders' => 'nullable|integer|min:0',
            'total_sales' => 'nullable|numeric|min:0',
            'total_discounts' => 'nullable|numeric|min:0',
            'total_tax' => 'nullable|numeric|min:0',
            'total_expenses' => 'nullable|numeric|min:0',
            'total_customers' => 'nullable|integer|min:0',
            'average_order_value' => 'nullable|numeric|min:0',
        ]);

        $summary->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Daily summary updated successfully.',
            'data' => $summary,
        ]);
    }

    /**
     * Remove the specified daily summary.
     */
    public function destroy(string $id)
    {
        $summary = is_numeric($id) 
            ? DailySummary::find($id) 
            : DailySummary::whereDate('summary_date', $id)->first();

        if (!$summary) {
            return response()->json([
                'success' => false,
                'message' => 'Daily summary not found.',
            ], 404);
        }

        $summary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Daily summary deleted successfully.',
        ]);
    }

    /**
     * Automatically aggregate/generate summary for a specific date from real orders.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'total_expenses' => 'nullable|numeric|min:0',
        ]);

        $targetDate = Carbon::parse($request->date)->format('Y-m-d');

        $orders = Order::whereDate('created_at', $targetDate)
            ->whereNotIn('status', ['cancelled', 'void'])
            ->get();

        $totalOrders = $orders->count();
        $totalSales = (float) $orders->sum('total_amount');
        $totalDiscounts = (float) $orders->sum('discount_amount');
        $totalTax = (float) $orders->sum('tax_amount');
        $totalExpenses = (float) ($request->total_expenses ?? 0.00);

        // Approximate unique customers by distinct table session or customer phone
        $totalCustomers = $orders->pluck('customer_phone')->filter()->unique()->count() ?: $totalOrders;
        $avgOrderValue = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0.00;

        $summary = DailySummary::updateOrCreate(
            ['summary_date' => $targetDate],
            [
                'total_orders' => $totalOrders,
                'total_sales' => $totalSales,
                'total_discounts' => $totalDiscounts,
                'total_tax' => $totalTax,
                'total_expenses' => $totalExpenses,
                'total_customers' => $totalCustomers,
                'average_order_value' => $avgOrderValue,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Daily summary for {$targetDate} generated successfully.",
            'data' => $summary,
        ]);
    }
}
