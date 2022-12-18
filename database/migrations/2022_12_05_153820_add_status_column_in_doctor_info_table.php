<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnInDoctorInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_info', function (Blueprint $table) {
            $table->enum('status', ['open', 'accepted', 'rejected'])->default('open')->after('type_doctor_id');
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
            $table->dropColumn('status');
        });
    }
}
