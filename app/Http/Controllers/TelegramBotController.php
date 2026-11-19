<?php

namespace App\Http\Controllers;

use App\Models\TelegramBot;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    /**
     * Display a listing of telegram bots.
     */
    public function index(Request $request)
    {
        $query = TelegramBot::query();

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $bots = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $bots,
        ]);
    }

    /**
     * Store a newly created telegram bot.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'bot_token' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $bot = TelegramBot::create([
            'name' => $validated['name'] ?? null,
            'bot_token' => $validated['bot_token'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telegram bot created successfully.',
            'data' => $bot,
        ], 201);
    }

    /**
     * Display the specified telegram bot.
     */
    public function show(string $id)
    {
        $bot = TelegramBot::find($id);

        if (!$bot) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram bot not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bot,
        ]);
    }

    /**
     * Update the specified telegram bot.
     */
    public function update(Request $request, string $id)
    {
        $bot = TelegramBot::find($id);

        if (!$bot) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram bot not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'bot_token' => 'sometimes|required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $bot->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Telegram bot updated successfully.',
            'data' => $bot,
        ]);
    }

    /**
     * Remove the specified telegram bot.
     */
    public function destroy(string $id)
    {
        $bot = TelegramBot::find($id);

        if (!$bot) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram bot not found.',
            ], 404);
        }

        $bot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Telegram bot deleted successfully.',
        ]);
    }
}
