<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'receipt_number',
        'total',
        'issued_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    /**
     * Get the order that owns the receipt.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

