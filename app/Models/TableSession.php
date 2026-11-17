<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    use HasFactory;

    protected $table = 'table_sessions';

    protected $fillable = [
        'dining_table_id',
        'session_token',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function diningTable()
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_session_id');
    }
}
