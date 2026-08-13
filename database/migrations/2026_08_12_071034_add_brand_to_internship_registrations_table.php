<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            // Brand ditentukan admin saat menerima pendaftar
            // Contoh nilai: 'magangjogja.com', 'areakerja.com', 'seven inc', dst.
            $table->string('brand')->nullable()->after('internship_status');
        });
    }

    public function down(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
