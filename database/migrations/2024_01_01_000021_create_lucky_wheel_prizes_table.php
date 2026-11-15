<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lucky_wheel_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->onDelete('cascade');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->enum('prize_type', ['free_product', 'discount_percentage', 'discount_fixed', 'coupon', 'try_again'])->default('free_product');
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('coupon_code', 50)->nullable();
            $table->integer('probability')->default(10); // Weight for random spin
            $table->string('slice_color', 30)->default('#2563EB'); // Wheel segment UI color
            $table->string('text_color', 30)->default('#FFFFFF');
            $table->string('icon')->nullable();
            $table->integer('quantity')->nullable(); // Total stock, null = unlimited
            $table->integer('remaining_quantity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_wheel_prizes');
    }
};

