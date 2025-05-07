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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            
            // Core fields
            $table->date('visit_date')->default(now());
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('clinic_id')->constrained('clinics');
            $table->foreignId('doctor_id')->constrained('doctors');
            
            // Queue number fields
            $table->string('queue_number')->unique(); // Format: P-[clinic_code]-[yyMMdd]-[increment]
            $table->integer('queue_order')->comment('Daily increment per clinic');
            
            // Visit classification
            $table->enum('visit_type', ['new', 'returning', 'control']);
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled'])
                  ->default('waiting');
            
            // Optional fields
            $table->text('remarks')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('visit_date');
            $table->index('patient_id');
            $table->index('clinic_id');
            $table->index('doctor_id');
            $table->index('status');
            
            // Unique constraint for queue number generation
            $table->unique(['visit_date', 'clinic_id', 'queue_order'], 'unique_daily_queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
