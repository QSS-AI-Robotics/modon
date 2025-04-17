<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeoLocationTable extends Migration
{
    public function up()
    {
        Schema::create('geo_location', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->decimal('latitude', 10, 7)->check('latitude BETWEEN -90 AND 90');
            $table->decimal('longitude', 10, 7)->check('longitude BETWEEN -180 AND 180');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('location_id')
                ->references('id')->on('locations')
                ->onDelete('cascade'); // deletes geo_location if location is deleted
        });
    }

    public function down()
    {
        Schema::dropIfExists('geo_location');
    }
}

