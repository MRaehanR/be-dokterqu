<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApotekInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apotek_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('province_id');
            $table->integer('city_id');
            $table->string('name', 50);
            $table->string('address', 100);
            $table->string('ktp');
            $table->string('npwp');
            $table->string('surat_izin_usaha');
            $table->text('image')->nullable();
            $table->double('latitude');
            $table->double('longitude');
            $table->timestamps();

            $table->foreign('province_id')->references('prov_id')->on('provinces')->cascadeOnUpdate();
            $table->foreign('city_id')->references('city_id')->on('cities')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apotek_info');
    }
}
