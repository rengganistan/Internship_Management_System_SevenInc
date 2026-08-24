<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webinars', function (Blueprint $table) {
            // JSON array kode brand yang boleh ikut webinar ini.
            // null = semua brand boleh ikut.
            $table->json('allowed_brands')->nullable()->after('certificate_brand');
        });
    }

    public function down(): void
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropColumn('allowed_brands');
        });
    }
};
