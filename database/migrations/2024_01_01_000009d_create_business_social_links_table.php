<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('social_id')->constrained('socials')->onDelete('cascade');
            $table->string('value', 255);
            $table->timestamps();

            $table->unique(['business_id', 'social_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_social_links');
    }
};

