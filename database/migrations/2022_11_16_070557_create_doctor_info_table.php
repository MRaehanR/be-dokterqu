<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_info', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->integer('type_doctor_id');
            $table->integer('experience');
            $table->string('alumnus');
            $table->integer('alumnus_tahun');
            $table->string('tempat_praktik');
            $table->string('cv');
            $table->string('str');
            $table->string('ktp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctor_info');
    }
}
