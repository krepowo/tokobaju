<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // no-op: kolom no_telepon dan role sudah didefinisikan langsung pada create_users_table
    }

    public function down(): void
    {
    }
};
