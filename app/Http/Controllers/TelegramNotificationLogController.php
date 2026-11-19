<?php

namespace App\Http\Controllers;

use App\Models\TelegramNotificationLog;
use Illuminate\Http\Request;

class TelegramNotificationLogController extends Controller
{
    /**
     * Display a listing of notification logs.
     */
    public function index(Request $request)
    {
        $query = TelegramNotificationLog::with(['telegramUser.user']);

        if ($request->has('telegram_user_id')) {
            $query->where('telegram_user_id', $request->telegram_user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('notification_type')) {
            $query->where('notification_type', $request->notification_type);
        }

        $logs = $query->latest('created_at')->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Store a newly created notification log.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'telegram_user_id' => 'required|exists:telegram_users,id',
            'notification_type' => 'nullable|string|max:100',
            'message' => 'required|string',
            'status' => 'nullable|string|max:50',
            'sent_at' => 'nullable|date',
        ]);

        $log = TelegramNotificationLog::create([
            'telegram_user_id' => $validated['telegram_user_id'],
            'notification_type' => $validated['notification_type'] ?? 'system_alert',
            'message' => $validated['message'],
            'status' => $validated['status'] ?? 'sent',
            'sent_at' => $validated['sent_at'] ?? now(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram notification log created successfully.',
            'data' => $log->load('telegramUser.user'),
        ], 201);
    }

    /**
     * Display the specified notification log.
     */
    public function show(string $id)
    {
        $log = TelegramNotificationLog::with(['telegramUser.user'])->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram notification log not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * Remove the specified notification log.
     */
    public function destroy(string $id)
    {
        $log = TelegramNotificationLog::find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram notification log not found.',
            ], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Telegram notification log deleted successfully.',
        ]);
    }
}
