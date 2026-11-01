<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->uuid('id_produk')->primary();
            $table->foreignUuid('id_kategori')->references('id_kategori')->on('kategori')->cascadeOnDelete();
            $table->foreignUuid('id_penjual')->references('id_user')->on('users')->cascadeOnDelete();
            $table->string('nama_produk', 150);
            $table->text('deskripsi');
            $table->string('gambar', 255);
            $table->decimal('harga', 12, 2);
            $table->text('spesifikasi_ukuran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
