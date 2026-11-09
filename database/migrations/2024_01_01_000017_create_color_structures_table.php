<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('color_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->onDelete('cascade');
            $table->string('theme_name')->default('Default Theme');
            $table->string('primary_color', 7)->default('#2563EB');
            $table->string('secondary_color', 7)->default('#60A5FA');
            $table->string('accent_color', 7)->default('#F59E0B');
            $table->string('background_color', 7)->default('#F8FAFC');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('color_structures');
    }
};

