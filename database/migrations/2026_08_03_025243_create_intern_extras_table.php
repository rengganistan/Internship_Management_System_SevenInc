<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk menyimpan akses eksklusif per pemagang:
     * - Surat Rekomendasi (file PDF)
     * - Link Grup Alumni
     * - Info Kerja (link/teks)
     *
     * Admin yang menentukan siapa yang mendapat akses ini.
     * Hanya tersedia setelah status = completed.
     */
    public function up(): void
    {
        Schema::create('intern_extras', function (Blueprint $table) {
            $table->id();

            // Terhubung ke internship_registrations
            $table->foreignId('internship_registration_id')
                ->constrained('internship_registrations')
                ->onDelete('cascade');

            // Surat Rekomendasi — path file PDF di storage
            $table->string('rekomendasi_path')->nullable();
            $table->string('rekomendasi_url')->nullable();
            $table->timestamp('rekomendasi_granted_at')->nullable();

            // Link Grup Alumni
            $table->string('alumni_group_url')->nullable();
            $table->string('alumni_group_label')->nullable()->default('Grup Alumni Seveninc');
            $table->timestamp('alumni_group_granted_at')->nullable();

            // Info Kerja
            $table->string('job_info_url')->nullable();
            $table->text('job_info_description')->nullable();
            $table->timestamp('job_info_granted_at')->nullable();

            $table->timestamps();

            // Satu record per intern
            $table->unique('internship_registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intern_extras');
    }
};
