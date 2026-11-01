<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id_user')->primary();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('no_telepon', 20)->nullable()->unique();
            $table->string('password', 255);
            $table->enum('role', ['pelanggan', 'penjual'])->default('pelanggan');
            $table->string('provinsi', 100)->nullable();
            $table->string('kota_kabupaten', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kelurahan_desa', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->text('detail_alamat')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
