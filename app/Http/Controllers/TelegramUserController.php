<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use Illuminate\Http\Request;

class TelegramUserController extends Controller
{
    /**
     * Display a listing of telegram users.
     */
    public function index(Request $request)
    {
        $query = TelegramUser::with(['user']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('telegram_chat_id')) {
            $query->where('telegram_chat_id', $request->telegram_chat_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Store a newly created telegram user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'telegram_chat_id' => 'required|string|max:100|unique:telegram_users,telegram_chat_id',
            'telegram_username' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $telegramUser = TelegramUser::create([
            'user_id' => $validated['user_id'] ?? null,
            'telegram_chat_id' => $validated['telegram_chat_id'],
            'telegram_username' => $validated['telegram_username'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram user registered successfully.',
            'data' => $telegramUser->load('user'),
        ], 201);
    }

    /**
     * Display the specified telegram user.
     */
    public function show(string $id)
    {
        $telegramUser = TelegramUser::with(['user', 'notificationLogs'])->find($id);

        if (!$telegramUser) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram user not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $telegramUser,
        ]);
    }

    /**
     * Update the specified telegram user.
     */
    public function update(Request $request, string $id)
    {
        $telegramUser = TelegramUser::find($id);

        if (!$telegramUser) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram user not found.',
            ], 404);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'telegram_chat_id' => 'sometimes|required|string|max:100|unique:telegram_users,telegram_chat_id,' . $id,
            'telegram_username' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $telegramUser->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Telegram user updated successfully.',
            'data' => $telegramUser->load('user'),
        ]);
    }

    /**
     * Remove the specified telegram user.
     */
    public function destroy(string $id)
    {
        $telegramUser = TelegramUser::find($id);

        if (!$telegramUser) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram user not found.',
            ], 404);
        }

        $telegramUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'Telegram user deleted successfully.',
        ]);
    }
}
