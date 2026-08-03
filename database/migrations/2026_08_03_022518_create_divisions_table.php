<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // Nama divisi: "Programmer", "UI/UX", dll
            $table->string('slug')->unique();     // Slug untuk value di form: "programmer", "uiux"
            $table->boolean('is_active')->default(true);  // Aktif/nonaktif
            $table->integer('sort_order')->default(0);    // Urutan tampil
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
