<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'code',
        'price',
        'tag',
        'manage_stock',
        'allow_stock_negative',
        'image',
        'promotion_id',
    ];

    protected $casts = [
        'price' => 'decimal:3',
        'manage_stock' => 'boolean',
        'allow_stock_negative' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
    }

    public function languages()
    {
        return $this->hasMany(ProductLanguage::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function luckyWheelPrizes()
    {
        return $this->hasMany(LuckyWheelPrize::class);
    }
}
