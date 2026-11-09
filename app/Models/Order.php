<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Supported order types
    public const TYPE_DINE_IN = 'dine_in';
    public const TYPE_TAKEAWAY = 'takeaway';
    public const TYPE_DELIVERY = 'delivery';

    protected $fillable = [
        'branch_id',
        'order_number',
        'order_type',
        'table_number',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'delivery_fee',
        'total_amount',
        'discount_amount',
        'final_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}
