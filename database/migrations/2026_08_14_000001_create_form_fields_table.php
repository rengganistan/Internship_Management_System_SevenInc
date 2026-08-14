<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk menyimpan konfigurasi field dinamis pada form pendaftaran pemagang.
     *
     * Setiap baris merepresentasikan satu pertanyaan/field di form.
     * Admin bisa menambah, mengedit label, mengubah urutan, mengaktifkan/nonaktifkan,
     * atau menghapus field yang tidak diperlukan.
     *
     * field_key    → nama kolom di internship_registrations (atau 'custom_{id}' untuk field baru)
     * field_type   → text | textarea | select | radio | checkbox | date | file | email | tel | hidden
     * label        → label yang tampil di form pemagang
     * placeholder  → placeholder input
     * options      → JSON array opsi (untuk select/radio/checkbox)
     * is_required  → apakah wajib diisi
     * is_active    → tampil/tidak tampil di form
     * is_system    → true = field inti (tidak bisa dihapus, hanya bisa edit label)
     * group_name   → pengelompokan visual (null = grup utama, 'informasi_tambahan' = bagian bawah)
     * sort_order   → urutan tampil
     * column_span  → lebar kolom: 1 = full width, 2 = half (grid 2 kolom)
     */
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_key')->unique();       // key unik → map ke kolom DB
            $table->string('field_type', 50);            // tipe input
            $table->string('label');                     // label pertanyaan
            $table->string('placeholder')->nullable();   // placeholder
            $table->json('options')->nullable();         // opsi dropdown/radio/checkbox
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // field inti, tidak bisa dihapus
            $table->string('group_name', 100)->nullable(); // pengelompokan visual
            $table->integer('sort_order')->default(0);
            $table->smallInteger('column_span')->default(1); // 1=full, 2=half grid
            $table->string('helper_text')->nullable();    // teks bantuan di bawah field
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
