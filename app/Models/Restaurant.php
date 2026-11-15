<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'phone',
        'email',
        'address',
        'logo',
        'status',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function luckyWheelPrizes()
    {
        return $this->hasMany(LuckyWheelPrize::class);
    }

    public function luckyWheelSpins()
    {
        return $this->hasMany(LuckyWheelSpin::class);
    }

    public function socialLinks()
    {
        return $this->hasMany(BusinessSocialLink::class, 'business_id');
    }
}

