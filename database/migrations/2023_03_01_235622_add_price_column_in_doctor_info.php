<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceColumnInDoctorInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_info', function (Blueprint $table) {
            $table->string('slug')->after('type_doctor_id');
            $table->decimal('price_homecare', 13)->nullable()->after('tempat_praktik');
            $table->boolean('is_available')->default(false)->after('price_homecare');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctor_info', function (Blueprint $table) {
            $table->dropColumn(['price_homecare', 'is_available', 'slug']);
        });
    }
}
