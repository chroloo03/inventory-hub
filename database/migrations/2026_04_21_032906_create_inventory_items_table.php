<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');         // e.g. "laptop", "book", "furniture"
            $table->string('status')->default('available'); // available | checked_out | maintenance
            $table->integer('quantity')->default(1);
            $table->json('attributes');          // flexible, schema-free column
            $table->timestamps();

            // Index the category for fast filtering
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
