<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'theme_name',
        'primary_color',
        'secondary_color',
        'accent_color',
        'background_color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship to Restaurant (optional: per-restaurant branding)
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}

