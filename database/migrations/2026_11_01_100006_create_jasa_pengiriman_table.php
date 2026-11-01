<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jasa_pengiriman', function (Blueprint $table) {
            $table->uuid('id_jasa')->primary();
            $table->uuid('id_penjual');
            $table->string('nama_jasa', 50);
            $table->timestamps();

            $table->foreign('id_penjual')->references('id_user')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jasa_pengiriman');
    }
};
