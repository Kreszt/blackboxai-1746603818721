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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('medical_journals');
            $table->string('prescription_number')->unique();
            $table->text('notes')->nullable();
            
            // Status management
            $table->enum('status', ['draft', 'revised', 'final'])
                  ->default('draft');
            
            // Revision tracking
            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revised_reason')->nullable();
            
            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('journal_id');
            $table->index('prescription_number');
            $table->index('status');
            $table->index('created_at');

            // Prevent duplicate prescriptions for same visit
            $table->unique(['journal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
