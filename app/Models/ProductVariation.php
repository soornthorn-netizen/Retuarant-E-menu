<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'product_unit_id',
        'unit_type_id',
        'is_multiplier',
        'variant_group',
        'price',
        'image',
        'tag',
        'promotion_id',
    ];

    protected $casts = [
        'price' => 'decimal:3',
        'is_multiplier' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function languages()
    {
        return $this->hasMany(ProductVariationLanguage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
