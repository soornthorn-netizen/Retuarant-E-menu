<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramBot extends Model
{
    use HasFactory;

    protected $table = 'telegram_bots';

    protected $fillable = [
        'name',
        'bot_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
