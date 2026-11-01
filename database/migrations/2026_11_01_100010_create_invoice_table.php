<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->uuid('id_invoice')->primary();
            $table->uuid('id_pesanan')->unique();
            $table->string('no_invoice', 30)->unique();
            $table->dateTime('tanggal_terbit');
            $table->decimal('total_tagihan', 12, 2);
            $table->string('rekening_tujuan', 150);
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
