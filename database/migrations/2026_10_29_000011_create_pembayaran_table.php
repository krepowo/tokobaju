<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->uuid('id_pembayaran')->primary();
            $table->foreignUuid('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnDelete();
            $table->foreignUuid('diverifikasi_oleh')->nullable()->references('id_user')->on('users')->nullOnDelete();
            $table->string('bukti_transfer', 255);
            $table->dateTime('tanggal_upload');
            $table->enum('status_verifikasi', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('alasan_tolak')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
