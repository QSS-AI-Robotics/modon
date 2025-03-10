<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_type_id')->constrained('inspection_types')->onDelete('cascade'); // ✅ Link to inspection_types
            $table->timestamp('start_datetime')->nullable(false);
            $table->timestamp('end_datetime')->nullable(false);
            $table->text('note')->nullable();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->timestamps();
        });

        // ✅ Pivot table for many-to-many relationship with locations
        Schema::create('mission_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('mission_location'); // ✅ Drop pivot table first
        Schema::dropIfExists('missions');
    }
};
