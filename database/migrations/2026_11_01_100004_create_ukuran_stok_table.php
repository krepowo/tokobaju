<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ukuran_stok', function (Blueprint $table) {
            $table->uuid('id_ukuran')->primary();
            $table->uuid('id_produk');
            $table->string('ukuran', 10);
            $table->string('warna', 30);
            $table->string('gambar', 255)->nullable();
            $table->unsignedInteger('stok')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->foreign('id_produk')->references('id_produk')->on('produk')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ukuran_stok');
    }
};
