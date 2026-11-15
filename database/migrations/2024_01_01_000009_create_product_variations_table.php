<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name', 255);
            $table->unsignedBigInteger('product_unit_id')->nullable();
            $table->foreignId('unit_type_id')->nullable()->constrained('unit_types')->onDelete('set null');
            $table->boolean('is_multiplier')->default(false);
            $table->string('variant_group', 100)->nullable();
            $table->decimal('price', 12, 3);
            $table->string('image', 255)->nullable();
            $table->string('tag', 100)->nullable();
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};

