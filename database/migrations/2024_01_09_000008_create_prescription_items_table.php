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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications');
            $table->integer('quantity');
            $table->string('dosage_instruction');
            $table->decimal('price', 10, 2)->comment('Price at the time of entry');
            $table->timestamps();

            // Indexes
            $table->index('prescription_id');
            $table->index('medication_id');

            // Prevent duplicate medications in same prescription
            $table->unique(['prescription_id', 'medication_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
