<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariationLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variation_id',
        'language_id',
        'name',
    ];

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}

