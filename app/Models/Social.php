<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
    ];

    public function businessLinks()
    {
        return $this->hasMany(BusinessSocialLink::class);
    }
}

