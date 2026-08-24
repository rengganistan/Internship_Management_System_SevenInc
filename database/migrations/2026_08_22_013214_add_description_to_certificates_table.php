<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Teks deskripsi custom untuk sertifikat webinar,
            // e.g. "Atas partisipasinya sebagai Peserta dalam Webinar ..."
            // null = template gunakan teks default otomatis
            $table->text('description')->nullable()->after('company');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
