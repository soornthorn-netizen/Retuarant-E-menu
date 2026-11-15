<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'social_id',
        'value',
    ];

    public function business()
    {
        return $this->belongsTo(Restaurant::class, 'business_id');
    }

    public function social()
    {
        return $this->belongsTo(Social::class);
    }
}

