<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    protected $table = 'telegram_users';

    protected $fillable = [
        'user_id',
        'telegram_chat_id',
        'telegram_username',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(TelegramNotificationLog::class, 'telegram_user_id');
    }
}
