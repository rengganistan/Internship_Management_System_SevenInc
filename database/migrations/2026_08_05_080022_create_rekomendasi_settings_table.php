<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekomendasi_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('SEVEN INC.');
            $table->text('company_address')->nullable();
            $table->string('company_city')->default('Yogyakarta');
            $table->string('company_phone')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('leader_name')->default('Rekario Danny Sanjaya, S.Kom');
            $table->string('leader_title')->default('CEO');
            $table->string('company_brand')->default('Seven Inc (Magangjogja.com)');
            // Isi surat — template kalimat rekomendasi (bisa pakai placeholder)
            $table->text('body_template')->nullable();
            // Aset
            $table->string('logo_path')->nullable();
            $table->string('stamp_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_settings');
    }
};
