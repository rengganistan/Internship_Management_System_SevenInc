<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webinars', function (Blueprint $table) {
            // Deskripsi custom untuk sertifikat, e.g. "Atas partisipasi dalam kegiatan webinar ..."
            // null = pakai teks default otomatis dari judul webinar
            $table->text('certificate_description')->nullable()->after('certificate_brand');
        });
    }

    public function down(): void
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropColumn('certificate_description');
        });
    }
};
