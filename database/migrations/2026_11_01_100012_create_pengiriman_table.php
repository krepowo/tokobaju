<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->uuid('id_pengiriman')->primary();
            $table->uuid('id_pesanan')->unique();
            $table->string('no_resi', 50);
            $table->dateTime('tanggal_kirim');
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
