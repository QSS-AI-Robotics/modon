<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveStartEndDatetimeFromPilotReportsTable extends Migration
{
    public function up()
    {
        Schema::table('pilot_reports', function (Blueprint $table) {
            $table->dropColumn(['start_datetime', 'end_datetime']);
        });
    }

    public function down()
    {
        Schema::table('pilot_reports', function (Blueprint $table) {
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
        });
    }
}