<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            $hasRoleId = Schema::hasColumn('users', 'role_id');
            $hasIsActive = Schema::hasColumn('users', 'is_active');
            $hasPhone = Schema::hasColumn('users', 'phone');

            if (!$hasRoleId || !$hasIsActive || !$hasPhone) {
                Schema::table('users', function (Blueprint $table) use ($hasRoleId, $hasIsActive, $hasPhone) {
                    if (!$hasRoleId) {
                        $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
                    }
                    if (!$hasPhone) {
                        $table->string('phone', 30)->nullable();
                    }
                    if (!$hasIsActive) {
                        $table->boolean('is_active')->default(true);
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'role_id')) {
                    $table->dropForeign(['role_id']);
                    $table->dropColumn('role_id');
                }
                if (Schema::hasColumn('users', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
