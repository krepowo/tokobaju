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
            $table->foreignUuid('id_produk')->references('id_produk')->on('produk')->cascadeOnDelete();
            $table->string('ukuran', 10);
            $table->string('warna', 30);
            $table->unsignedInteger('stok')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ukuran_stok');
    }
};
