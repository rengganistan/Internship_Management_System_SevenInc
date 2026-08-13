<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('form_key')->default('internship_registration'); // form yang dipasangi
            $table->string('label');            // teks label/pertanyaan
            $table->string('field_key')->unique(); // identifier unik, slug format
            $table->string('type')->default('text'); // text|textarea|number|date|email|phone|select|radio|checkbox
            $table->string('placeholder')->nullable(); // keterangan/helper text
            $table->string('section')->default('Field Tambahan'); // section grouping
            $table->json('options')->nullable();   // untuk select/radio/checkbox: [{label,value}]
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(999);
            $table->softDeletes(); // soft delete — aman untuk data existing
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_custom_fields');
    }
};
