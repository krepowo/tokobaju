<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->uuid('id_pesanan')->primary();
            $table->uuid('id_user');
            $table->uuid('id_jasa');
            $table->dateTime('tanggal_pesanan');
            $table->text('alamat_tujuan');
            $table->string('pulau_tujuan', 50);
            $table->decimal('ongkir', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('status', [
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'pembayaran_ditolak',
                'diproses',
                'dikirim',
                'selesai',
                'dibatalkan',
            ])->default('menunggu_pembayaran');
            $table->dateTime('batas_bayar')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreign('id_jasa')->references('id_jasa')->on('jasa_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
