<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatesTable extends Migration
{
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('division');
            $table->string('company');
            $table->string('background_image')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('city');
            $table->string('brand');
            $table->string('serial_number')->unique();
            $table->string('logo1')->nullable();
            $table->string('logo2')->nullable();
            $table->string('signature_image1')->nullable();
            $table->string('signature_image2')->nullable();
            $table->string('name_signatory1');
            $table->string('name_signatory2')->nullable();
            $table->string('role1');
            $table->string('role2')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
}
