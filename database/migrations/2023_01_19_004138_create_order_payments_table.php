<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_detail_id');
            $table->string('transaction_id');
            $table->string('status');
            $table->string('status_code');
            $table->string('payment_type');
            $table->decimal('payment_amount', 13);
            $table->timestamp('settlement_time')->nullable();
            $table->text('json_data');
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
        Schema::dropIfExists('order_payments');
    }
}
