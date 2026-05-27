<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Tracks when we last sent a low stock alert for this item.
            // NULL means never notified or stock was restored above threshold.
            $table->timestamp('low_stock_notified_at')->nullable()->after('low_stock_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('low_stock_notified_at');
        });
    }
};
