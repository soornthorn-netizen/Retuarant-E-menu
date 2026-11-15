<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function categoryLanguages()
    {
        return $this->hasMany(CategoryLanguage::class);
    }

    public function productLanguages()
    {
        return $this->hasMany(ProductLanguage::class);
    }

    public function variationLanguages()
    {
        return $this->hasMany(ProductVariationLanguage::class);
    }
}

