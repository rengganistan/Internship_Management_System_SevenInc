<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webinar_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('webinar_id')
                ->constrained('webinars')
                ->cascadeOnDelete();

            $table->foreignId('user_id')                    // Pemagang
                ->constrained('users')
                ->cascadeOnDelete();

            // Bukti kehadiran
            $table->string('proof_file')->nullable();       // Path foto/screenshot
            $table->text('proof_note')->nullable();         // Catatan dari pemagang

            // Status review oleh admin
            // pending = belum diproses, approved = disetujui, rejected = ditolak
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            $table->text('rejection_reason')->nullable();   // Alasan ditolak
            $table->foreignId('reviewed_by')               // Admin yang review
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // Sertifikat yang dihasilkan setelah approved
            $table->foreignId('certificate_id')            // FK ke tabel certificates
                ->nullable()
                ->constrained('certificates')
                ->nullOnDelete();

            $table->timestamps();

            // Satu pemagang hanya bisa submit satu kali per webinar
            $table->unique(['webinar_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webinar_attendances');
    }
};
