<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif_pengiriman', function (Blueprint $table) {
            $table->uuid('id_tarif')->primary();
            $table->foreignUuid('id_jasa')->references('id_jasa')->on('jasa_pengiriman')->cascadeOnDelete();
            $table->string('pulau_tujuan', 50);
            $table->decimal('tarif', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_pengiriman');
    }
};
