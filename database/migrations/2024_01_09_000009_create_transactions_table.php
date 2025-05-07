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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('patient_id')->constrained('patients');
            $table->date('visit_date');
            
            // Payment details
            $table->enum('status', ['unpaid', 'paid', 'canceled'])
                  ->default('unpaid');
            $table->enum('payment_method', ['cash', 'bpjs', 'insurance'])
                  ->nullable();
            
            // Amount calculations
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            
            // State
            $table->boolean('is_completed')->default(false);
            
            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transaction_number');
            $table->index('patient_id');
            $table->index('visit_date');
            $table->index('status');
            $table->index('payment_method');
            $table->index('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
