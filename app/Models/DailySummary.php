<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    use HasFactory;

    protected $table = 'daily_summaries';

    protected $fillable = [
        'summary_date',
        'total_orders',
        'total_sales',
        'total_discounts',
        'total_tax',
        'total_expenses',
        'total_customers',
        'average_order_value',
    ];

    protected $casts = [
        'summary_date' => 'date:Y-m-d',
        'total_orders' => 'integer',
        'total_sales' => 'decimal:2',
        'total_discounts' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_customers' => 'integer',
        'average_order_value' => 'decimal:2',
    ];
}
