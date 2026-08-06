<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();

            $table->string('title');                        // Judul webinar
            $table->text('description')->nullable();        // Deskripsi
            $table->datetime('event_date');                 // Tanggal & waktu pelaksanaan
            $table->datetime('event_end_date')->nullable(); // Tanggal selesai (opsional)
            $table->string('zoom_link')->nullable();        // Link Zoom / meeting
            $table->string('platform')->default('Zoom');   // Zoom / Google Meet / dll

            // Sertifikat config — reuse field dari tabel certificates
            $table->string('certificate_background')->nullable(); // path bg_ image
            $table->string('certificate_logo1')->nullable();
            $table->string('certificate_logo2')->nullable();
            $table->string('certificate_signature1')->nullable();
            $table->string('certificate_signature2')->nullable();
            $table->string('certificate_signatory1_name')->nullable();
            $table->string('certificate_signatory1_role')->nullable();
            $table->string('certificate_signatory2_name')->nullable();
            $table->string('certificate_signatory2_role')->nullable();
            $table->string('certificate_company')->default('Seven Inc');
            $table->string('certificate_city')->default('Yogyakarta');
            $table->string('certificate_brand')->default('SI');

            $table->boolean('is_active')->default(true);   // Published / draft
            $table->foreignId('created_by')                // Admin yang buat
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webinars');
    }
};
