<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'opening_time',
        'closing_time',
        'status',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function diningTables()
    {
        return $this->hasMany(DiningTable::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function luckyWheelSpins()
    {
        return $this->hasMany(LuckyWheelSpin::class);
    }
}
