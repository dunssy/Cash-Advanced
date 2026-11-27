<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_advance_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained('cash_advances')->cascadeOnDelete();
            $table->foreignId('giver_id')->constrained('users')->cascadeOnDelete(); // Atasan yang membagi uang
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete(); // Karyawan penerima uang
            $table->decimal('amount', 15, 2);
            $table->timestamp('distributed_at')->useCurrent();
            $table->string('transfer_proof')->nullable(); // Foto / file bukti transfer/serah terima kas
            $table->text('notes')->nullable();
            $table->string('status')->default('COMPLETED')->index(); // COMPLETED, ACKNOWLEDGED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advance_distributions');
    }
};
