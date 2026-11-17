<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of role permission assignments.
     */
    public function index(Request $request)
    {
        $query = RolePermission::with(['role', 'permission']);

        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('permission_id')) {
            $query->where('permission_id', $request->permission_id);
        }

        $assignments = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    /**
     * Assign a permission to a role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $assignment = RolePermission::firstOrCreate(
            [
                'role_id' => $validated['role_id'],
                'permission_id' => $validated['permission_id'],
            ],
            [
                'created_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Permission assigned to role successfully.',
            'data' => $assignment->load(['role', 'permission']),
        ], 201);
    }

    /**
     * Remove permission from role.
     */
    public function destroy(Request $request, string $id)
    {
        // Support either by ID or query params
        $assignment = RolePermission::find($id);

        if (!$assignment && $request->has(['role_id', 'permission_id'])) {
            $assignment = RolePermission::where('role_id', $request->role_id)
                ->where('permission_id', $request->permission_id)
                ->first();
        }

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Role permission assignment not found.',
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role permission removed successfully.',
        ]);
    }
}
