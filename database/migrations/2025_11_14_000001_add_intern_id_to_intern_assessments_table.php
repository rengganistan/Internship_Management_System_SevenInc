<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intern_assessments', function (Blueprint $table) {
            // Tambah kolom intern_id sebagai foreign key ke internship_registrations
            $table->unsignedBigInteger('intern_id')->nullable()->after('id');

            $table->foreign('intern_id')
                ->references('id')
                ->on('internship_registrations')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('intern_assessments', function (Blueprint $table) {
            $table->dropForeign(['intern_id']);
            $table->dropColumn('intern_id');
        });
    }
};
