<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('sku', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->decimal('price', 12, 3)->default(0);
            $table->string('tag', 100)->nullable();
            $table->boolean('manage_stock')->default(false);
            $table->boolean('allow_stock_negative')->default(false);
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('image', 255)->nullable();
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
