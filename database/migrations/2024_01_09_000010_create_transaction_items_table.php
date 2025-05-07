<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            
            // Item type and reference
            $table->enum('type', ['consultation', 'prescription', 'manual']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            
            // Item details
            $table->string('description');
            $table->decimal('price', 12, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            
            // Optional notes
            $table->text('notes')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index('transaction_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);

            // Prevent duplicate items for same reference
            $table->unique(['transaction_id', 'type', 'reference_id'], 'unique_transaction_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
