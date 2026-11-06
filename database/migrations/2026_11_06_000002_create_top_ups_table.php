<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('top_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained('cash_advances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            
            // Status: PENDING_ATASAN, PENDING_FINANCE, PENDING_DIREKTUR, APPROVED, DISBURSED, REJECTED
            $table->string('status')->default('PENDING_ATASAN')->index();
            $table->boolean('requires_direktur_approval')->default(false);

            // Approvals
            $table->foreignId('approved_by_atasan')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atasan_approved_at')->nullable();
            
            $table->foreignId('approved_by_finance')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finance_approved_at')->nullable();

            $table->foreignId('approved_by_direktur')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('direktur_approved_at')->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Disbursement by Finance
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disbursed_at')->nullable();
            $table->string('disbursement_ref')->nullable();
            $table->text('disbursement_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('top_ups');
    }
};
