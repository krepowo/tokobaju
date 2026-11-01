<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->uuid('id_detail')->primary();
            $table->foreignUuid('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnDelete();
            $table->foreignUuid('id_ukuran')->references('id_ukuran')->on('ukuran_stok')->restrictOnDelete();
            $table->unsignedSmallInteger('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
