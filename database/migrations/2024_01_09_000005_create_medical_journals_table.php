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
        Schema::create('medical_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records');
            $table->foreignId('visit_id')->constrained('visits');
            $table->string('journal_number')->unique();
            $table->date('date');
            $table->foreignId('doctor_id')->constrained('doctors');
            $table->foreignId('clinic_id')->constrained('clinics');
            
            // Medical information
            $table->text('complaint');
            $table->text('diagnosis');
            $table->text('treatment');
            $table->text('prescription')->nullable();
            $table->text('referral')->nullable();
            
            // Status
            $table->enum('status', ['ongoing', 'completed', 'referred'])
                  ->default('ongoing');

            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('medical_record_id');
            $table->index('visit_id');
            $table->index('journal_number');
            $table->index('date');
            $table->index('doctor_id');
            $table->index('clinic_id');
            $table->index('status');

            // Prevent duplicate entries per visit
            $table->unique(['visit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_journals');
    }
};
