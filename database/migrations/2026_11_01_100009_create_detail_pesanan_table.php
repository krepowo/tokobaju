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
            $table->uuid('id_pesanan');
            $table->uuid('id_ukuran');
            $table->unsignedSmallInteger('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnDelete();
            $table->foreign('id_ukuran')->references('id_ukuran')->on('ukuran_stok');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
