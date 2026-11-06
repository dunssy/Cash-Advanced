<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained('cash_advances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('transaction_date')->index();
            $table->string('description');
            $table->string('category')->index(); // material, transport, sewa_alat, konsumsi, lainnya
            $table->decimal('amount', 15, 2);
            $table->string('vendor_name');
            $table->string('vendor_tax_number')->nullable(); // No Faktur atau NPWP
            $table->text('notes')->nullable();

            // Talangan (Emergency out-of-pocket expense)
            $table->boolean('is_talangan')->default(false);
            $table->text('talangan_emergency_reason')->nullable();

            // Tanpa Struk (Lost receipt statement)
            $table->boolean('is_lost_receipt')->default(false);
            $table->string('lost_receipt_statement_file')->nullable();
            $table->foreignId('lost_receipt_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lost_receipt_approved_at')->nullable();

            // Perekaman terlambat (> 1 hari)
            $table->boolean('is_late')->default(false);

            // Verifikasi Finance (atau Direktur bila pemohon Finance)
            // Status: PENDING, VALID, NEEDS_REVISION, REJECTED, VOID
            $table->string('verification_status')->default('PENDING')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            // Void oleh Finance dengan alasan tertulis
            $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('void_at')->nullable();
            $table->text('void_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
