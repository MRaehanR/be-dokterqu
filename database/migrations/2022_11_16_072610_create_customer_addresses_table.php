<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('default')->default(0);
            $table->string('label', 20);
            $table->string('address', 200);
            $table->text('note')->nullable();
            $table->string('recipient', 20);
            $table->string('phone', 15);
            $table->integer('province_id');
            $table->integer('city_id');
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
        Schema::dropIfExists('customer_addresses');
    }
}
