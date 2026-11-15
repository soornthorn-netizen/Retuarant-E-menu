<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuckyWheelPrize extends Model
{
    use HasFactory;

    public const TYPE_FREE_PRODUCT = 'free_product';
    public const TYPE_DISCOUNT_PERCENTAGE = 'discount_percentage';
    public const TYPE_DISCOUNT_FIXED = 'discount_fixed';
    public const TYPE_COUPON = 'coupon';
    public const TYPE_TRY_AGAIN = 'try_again';

    protected $fillable = [
        'restaurant_id',
        'title',
        'description',
        'prize_type',
        'discount_value',
        'product_id',
        'coupon_code',
        'probability',
        'slice_color',
        'text_color',
        'icon',
        'quantity',
        'remaining_quantity',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'probability' => 'integer',
        'quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function spins()
    {
        return $this->hasMany(LuckyWheelSpin::class, 'prize_id');
    }
}

