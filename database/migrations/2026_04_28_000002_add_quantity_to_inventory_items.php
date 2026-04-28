<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Only add if they don't already exist (safe to run on existing tables)
            if (!Schema::hasColumn('inventory_items', 'quantity')) {
                $table->integer('quantity')->default(1)->after('status');
            }
            if (!Schema::hasColumn('inventory_items', 'low_stock_threshold')) {
                $table->integer('low_stock_threshold')->default(5)->after('quantity');
            }
            if (!Schema::hasColumn('inventory_items', 'status')) {
                $table->string('status')->default('available')->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'low_stock_threshold']);
        });
    }
};
