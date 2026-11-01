<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keranjang', function (Blueprint $table) {
            $table->uuid('id_keranjang')->primary();
            $table->foreignUuid('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreignUuid('id_ukuran')->references('id_ukuran')->on('ukuran_stok')->cascadeOnDelete();
            $table->unsignedSmallInteger('jumlah')->default(1);
            $table->boolean('dipilih')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};
