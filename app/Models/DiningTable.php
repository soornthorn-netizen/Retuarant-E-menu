<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiningTable extends Model
{
    use HasFactory;

    protected $table = 'dining_tables';

    protected $fillable = [
        'branch_id',
        'table_number',
        'capacity',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function tableSessions()
    {
        return $this->hasMany(TableSession::class, 'dining_table_id');
    }

    public function activeSession()
    {
        return $this->hasOne(TableSession::class, 'dining_table_id')
            ->where('status', 'active')
            ->latestOfMany();
    }
}
