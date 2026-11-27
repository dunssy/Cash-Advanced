<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable()->after('deleted_by');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->after('void_reason')->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable()->after('deleted_by');
        });

        Schema::table('top_ups', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->after('disbursement_notes')->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable()->after('deleted_by');
        });

        Schema::table('cash_advance_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('cash_advance_distributions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('expense_attachments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by', 'delete_reason']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by', 'delete_reason']);
        });

        Schema::table('top_ups', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by', 'delete_reason']);
        });

        Schema::table('cash_advance_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('cash_advance_distributions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('expense_attachments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
