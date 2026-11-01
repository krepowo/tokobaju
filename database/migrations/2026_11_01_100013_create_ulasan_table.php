<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan', function (Blueprint $table) {
            $table->uuid('id_ulasan')->primary();
            $table->uuid('id_user');
            $table->uuid('id_produk');
            $table->uuid('id_detail')->unique();
            $table->unsignedTinyInteger('rating');
            $table->text('komentar')->nullable();
            $table->dateTime('tanggal');
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreign('id_produk')->references('id_produk')->on('produk')->cascadeOnDelete();
            $table->foreign('id_detail')->references('id_detail')->on('detail_pesanan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};
