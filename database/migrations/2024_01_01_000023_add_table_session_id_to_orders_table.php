<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'table_session_id')) {
                    $table->foreignId('table_session_id')->nullable()->after('branch_id')->constrained('table_sessions')->nullOnDelete();
                }
                if (!Schema::hasColumn('orders', 'total')) {
                    $table->decimal('total', 10, 2)->default(0)->after('delivery_fee');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'table_session_id')) {
                    $table->dropForeign(['table_session_id']);
                    $table->dropColumn('table_session_id');
                }
                if (Schema::hasColumn('orders', 'total')) {
                    $table->dropColumn('total');
                }
            });
        }
    }
};
