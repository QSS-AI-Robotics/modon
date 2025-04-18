<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFieldsFromPilotReportImagesTable extends Migration
{
    public function up()
    {
        Schema::table('pilot_report_images', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['inspection_type_id']);
            $table->dropForeign(['location_id']);

            // Now drop columns
            $table->dropColumn(['inspection_type_id', 'location_id', 'description']);
        });
    }

    public function down()
    {
        Schema::table('pilot_report_images', function (Blueprint $table) {
            $table->unsignedBigInteger('inspection_type_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->text('description')->nullable();

            $table->foreign('inspection_type_id')->references('id')->on('inspection_types');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }
}


