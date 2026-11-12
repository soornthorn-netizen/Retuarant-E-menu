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
        'description',
        'base_price',
        'image',
        'is_available',
        'is_featured',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Backward-compatibility accessor for price -> base_price
     */
    public function getPriceAttribute()
    {
        return $this->base_price;
    }

    /**
     * Backward-compatibility mutator for price -> base_price
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['base_price'] = $value;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function imageItems()
    {
        return $this->hasMany(ImageItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
