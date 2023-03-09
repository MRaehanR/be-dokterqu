<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHomecaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_homecares', function (Blueprint $table) {
            $table->id();
            $table->string('order_detail_id');
            $table->foreignId('doctor_info_id')->constrained('doctor_info')->cascadeOnUpdate();
            $table->foreignId('operational_time_id')->constrained()->cascadeOnUpdate();
            $table->date('date');
            $table->enum('status', ['waiting_payment', 'open', 'accepted', 'rejected', 'on_going', 'canceled', 'finished']);
            $table->timestamps();

            $table->foreign('order_detail_id')->references('id')->on('order_details')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_homecares');
    }
}
