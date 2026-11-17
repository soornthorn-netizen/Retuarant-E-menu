<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableSessionController extends Controller
{
    /**
     * Display a listing of table sessions.
     */
    public function index(Request $request)
    {
        $query = TableSession::with(['diningTable', 'orders']);

        if ($request->has('dining_table_id')) {
            $query->where('dining_table_id', $request->dining_table_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->orderBy('opened_at', 'desc')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Store a newly created table session.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dining_table_id' => 'required|exists:dining_tables,id',
            'session_token' => 'nullable|string|max:255|unique:table_sessions,session_token',
            'status' => 'nullable|string|in:active,closed,completed,cancelled',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
        ]);

        $validated['session_token'] = $validated['session_token'] ?? 'SESS-' . strtoupper(Str::random(12));
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['opened_at'] = $validated['opened_at'] ?? now();

        $session = TableSession::create($validated);

        // Update the dining table to occupied
        DiningTable::where('id', $validated['dining_table_id'])->update(['status' => 'occupied']);

        return response()->json([
            'success' => true,
            'message' => 'Table session created successfully.',
            'data' => $session->load('diningTable'),
        ], 201);
    }

    /**
     * Display the specified table session.
     */
    public function show(string $id)
    {
        $session = TableSession::with(['diningTable', 'orders.orderItems.product'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    /**
     * Update the specified table session.
     */
    public function update(Request $request, string $id)
    {
        $session = TableSession::findOrFail($id);

        $validated = $request->validate([
            'dining_table_id' => 'sometimes|required|exists:dining_tables,id',
            'session_token' => 'sometimes|nullable|string|max:255|unique:table_sessions,session_token,' . $session->id,
            'status' => 'nullable|string|in:active,closed,completed,cancelled',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
        ]);

        $session->update($validated);

        // If status changed to closed/completed, free up table if no other active sessions
        if (isset($validated['status']) && in_array($validated['status'], ['closed', 'completed', 'cancelled'])) {
            $hasOtherActive = TableSession::where('dining_table_id', $session->dining_table_id)
                ->where('status', 'active')
                ->where('id', '!=', $session->id)
                ->exists();

            if (!$hasOtherActive) {
                DiningTable::where('id', $session->dining_table_id)->update(['status' => 'available']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Table session updated successfully.',
            'data' => $session->load('diningTable'),
        ]);
    }

    /**
     * Remove the specified table session.
     */
    public function destroy(string $id)
    {
        $session = TableSession::findOrFail($id);
        $diningTableId = $session->dining_table_id;
        $session->delete();

        // Check if table has remaining active sessions
        $hasActive = TableSession::where('dining_table_id', $diningTableId)
            ->where('status', 'active')
            ->exists();
        if (!$hasActive) {
            DiningTable::where('id', $diningTableId)->update(['status' => 'available']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Table session deleted successfully.',
        ]);
    }

    /**
     * Close the table session and mark table available.
     */
    public function closeSession(string $id)
    {
        $session = TableSession::findOrFail($id);
        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // Mark table as available
        DiningTable::where('id', $session->dining_table_id)->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => 'Table session closed successfully.',
            'data' => $session->load('diningTable'),
        ]);
    }

    /**
     * Get all orders associated with this session.
     */
    public function orders(string $id)
    {
        $session = TableSession::with(['diningTable', 'orders.orderItems.product', 'orders.payments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $session->orders,
        ]);
    }
}
