<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationalTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operational_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('type', ['homecare', 'apotek']);
            $table->enum('day', [
                0 => 'sunday',
                1 => 'monday',
                2 =>  'tuesday',
                3 => 'wednesday',
                4 =>  'thursday',
                5 => 'friday',
                6 => 'saturday',
            ]);
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->boolean('is_available')->default(false);
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
        Schema::dropIfExists('operational_times');
    }
}
