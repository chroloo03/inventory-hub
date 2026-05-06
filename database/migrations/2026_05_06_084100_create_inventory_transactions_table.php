<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                  ->constrained('inventory_items')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->enum('type', ['in', 'out']);

            $table->unsignedInteger('quantity');  // always positive; type determines direction

            $table->string('reason');             // e.g. "Restocked", "Damaged", "Issued to dept"

            $table->text('notes')->nullable();    // optional extra detail

            $table->unsignedInteger('quantity_before'); // snapshot for audit trail
            $table->unsignedInteger('quantity_after');  // snapshot for audit trail

            $table->timestamps();

            // Indexes for fast ledger queries
            $table->index(['inventory_item_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
