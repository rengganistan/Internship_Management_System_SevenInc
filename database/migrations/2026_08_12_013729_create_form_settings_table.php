<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_settings', function (Blueprint $table) {
            $table->id();
            $table->string('form_key')->unique(); // e.g. 'internship_registration'
            $table->json('fields');               // JSON array of field configs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_settings');
    }
};
