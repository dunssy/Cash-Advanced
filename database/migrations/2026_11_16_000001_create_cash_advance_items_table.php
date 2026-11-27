<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_advance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained('cash_advances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Pemohon item (karyawan/atasan)
            $table->string('category')->index(); // material, transport, sewa_alat, konsumsi, lainnya
            $table->string('item_name');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('unit'); // hari, org, ls, unit, malam, dll.
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('PENDING')->index(); // PENDING, APPROVED, REJECTED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advance_items');
    }
};
