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
            $table->foreignUuid('id_pesanan')->unique()->references('id_pesanan')->on('pesanan')->cascadeOnDelete();
            $table->string('no_resi', 50);
            $table->dateTime('tanggal_kirim');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
