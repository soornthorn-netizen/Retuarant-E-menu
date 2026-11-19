<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'telegram_notification_logs';

    public $timestamps = false;

    protected $fillable = [
        'telegram_user_id',
        'notification_type',
        'message',
        'status',
        'sent_at',
        'created_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }
}
