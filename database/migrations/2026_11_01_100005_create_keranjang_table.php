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
            $table->uuid('id_user');
            $table->uuid('id_ukuran');
            $table->unsignedSmallInteger('jumlah');
            $table->boolean('dipilih')->default(false);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreign('id_ukuran')->references('id_ukuran')->on('ukuran_stok')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};
