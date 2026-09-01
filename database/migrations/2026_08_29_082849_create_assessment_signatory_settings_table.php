<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_signatory_settings', function (Blueprint $table) {
            $table->id();

            // Brand/perusahaan — unik agar 1 brand = 1 setting
            $table->string('brand')->unique();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();

            // Penandatangan
            $table->string('signature_name')->nullable();
            $table->string('signature_position')->nullable();

            // File aset
            $table->string('signature_image_path')->nullable();
            $table->string('company_logo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_signatory_settings');
    }
};
