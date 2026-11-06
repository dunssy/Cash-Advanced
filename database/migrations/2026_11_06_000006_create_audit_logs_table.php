<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action')->index(); // LOGIN, LOGOUT, CA_CREATED, CA_APPROVED, CA_REJECTED, CA_DISBURSED, TOPUP_CREATED, TOPUP_APPROVED, TOPUP_DISBURSED, EXPENSE_CREATED, EXPENSE_UPDATED, EXPENSE_VERIFIED, EXPENSE_VOIDED, GEMBOK_OVERRIDE, CONFIG_UPDATED, etc.
            $table->string('record_type')->nullable()->index();
            $table->unsignedBigInteger('record_id')->nullable()->index();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
