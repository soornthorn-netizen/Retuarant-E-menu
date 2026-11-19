<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date')->unique();
            $table->integer('total_orders')->default(0);
            $table->decimal('total_sales', 12, 2)->default(0.00);
            $table->decimal('total_discounts', 12, 2)->default(0.00);
            $table->decimal('total_tax', 12, 2)->default(0.00);
            $table->decimal('total_expenses', 12, 2)->default(0.00);
            $table->integer('total_customers')->default(0);
            $table->decimal('average_order_value', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
