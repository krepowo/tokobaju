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
            $table->foreignUuid('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreignUuid('id_produk')->references('id_produk')->on('produk')->cascadeOnDelete();
            $table->foreignUuid('id_detail')->nullable()->unique()->references('id_detail')->on('detail_pesanan')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('komentar')->nullable();
            $table->dateTime('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};
