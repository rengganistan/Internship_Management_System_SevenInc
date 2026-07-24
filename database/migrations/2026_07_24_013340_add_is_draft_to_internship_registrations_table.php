<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom untuk fitur "Simpan sebagai Draft" di form pendaftaran magang.
     *
     * is_draft        → true = form belum dikirim (draft), false = sudah submit resmi
     * draft_saved_at  → kapan terakhir kali disimpan sebagai draft
     */
    public function up(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            // Boolean default false → semua data lama dianggap sudah submit
            $table->boolean('is_draft')->default(false)->after('internship_status');

            // Timestamp nullable → hanya terisi kalau pernah disimpan sebagai draft
            $table->timestamp('draft_saved_at')->nullable()->after('is_draft');
        });
    }

    public function down(): void
    {
        Schema::table('internship_registrations', function (Blueprint $table) {
            $table->dropColumn(['is_draft', 'draft_saved_at']);
        });
    }
};
