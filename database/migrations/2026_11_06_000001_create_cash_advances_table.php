<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->string('ca_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('project_name');
            $table->string('project_code')->nullable();
            $table->text('purpose');
            $table->decimal('requested_amount', 15, 2);
            $table->decimal('approved_amount', 15, 2)->nullable();
            
            // Statuses: PENDING_APPROVAL, REJECTED, ACTIVE, NEED_SETTLEMENT, PENDING_VERIFICATION, CLOSED
            $table->string('status')->default('PENDING_APPROVAL')->index();
            
            // Approval (Atasan / Direktur)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Rejection
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Disbursement (Finance)
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disbursed_at')->nullable();
            $table->string('disbursement_ref')->nullable(); // bank ref or voucher
            $table->text('disbursement_notes')->nullable();

            // Timeline & Deadlines (Default 2 months from disbursed_at)
            $table->timestamp('due_date')->nullable()->index();

            // Lock Override by Direktur (Exception)
            $table->boolean('is_unlocked')->default(false);
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->text('unlock_reason')->nullable();

            // Project completion & Settlement
            $table->timestamp('project_completed_at')->nullable();
            $table->decimal('settlement_refund_amount', 15, 2)->default(0);
            $table->string('settlement_refund_proof')->nullable(); // File path
            $table->timestamp('settlement_transferred_at')->nullable();
            $table->text('settlement_notes')->nullable();

            // Close (Finance / Direktur)
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};
