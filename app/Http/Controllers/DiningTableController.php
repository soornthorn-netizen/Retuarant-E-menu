<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiningTableController extends Controller
{
    /**
     * Display a listing of dining tables.
     */
    public function index(Request $request)
    {
        $query = DiningTable::with(['branch', 'activeSession']);

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('table_number', 'like', "%{$search}%");
        }

        $tables = $query->orderBy('table_number', 'asc')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    /**
     * Store a newly created dining table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'table_number' => 'required|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:available,occupied,reserved,maintenance',
        ]);

        $validated['capacity'] = $validated['capacity'] ?? 4;
        $validated['status'] = $validated['status'] ?? 'available';

        $diningTable = DiningTable::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dining table created successfully.',
            'data' => $diningTable->load('branch'),
        ], 201);
    }

    /**
     * Display the specified dining table with active session.
     */
    public function show(string $id)
    {
        $diningTable = DiningTable::with(['branch', 'activeSession.orders'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $diningTable,
        ]);
    }

    /**
     * Update the specified dining table.
     */
    public function update(Request $request, string $id)
    {
        $diningTable = DiningTable::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'table_number' => 'sometimes|required|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:available,occupied,reserved,maintenance',
        ]);

        $diningTable->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dining table updated successfully.',
            'data' => $diningTable->load('branch'),
        ]);
    }

    /**
     * Remove the specified dining table.
     */
    public function destroy(string $id)
    {
        $diningTable = DiningTable::findOrFail($id);
        $diningTable->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dining table deleted successfully.',
        ]);
    }

    /**
     * Get active session for a specific dining table.
     */
    public function activeSession(string $id)
    {
        $diningTable = DiningTable::findOrFail($id);
        $activeSession = $diningTable->activeSession()->with('orders.orderItems')->first();

        return response()->json([
            'success' => true,
            'data' => $activeSession,
        ]);
    }

    /**
     * Open a new dining session on this table.
     */
    public function openSession(Request $request, string $id)
    {
        $diningTable = DiningTable::findOrFail($id);

        // Check if there is already an active session
        $existingSession = $diningTable->activeSession()->first();
        if ($existingSession) {
            return response()->json([
                'success' => true,
                'message' => 'An active session already exists for this table.',
                'data' => $existingSession->load('orders'),
            ]);
        }

        $sessionToken = $request->input('session_token') ?? 'SESS-' . strtoupper(Str::random(12));

        $session = TableSession::create([
            'dining_table_id' => $diningTable->id,
            'session_token' => $sessionToken,
            'status' => 'active',
            'opened_at' => now(),
        ]);

        $diningTable->update(['status' => 'occupied']);

        return response()->json([
            'success' => true,
            'message' => 'Table session opened successfully.',
            'data' => $session->load('diningTable'),
        ], 201);
    }
}
