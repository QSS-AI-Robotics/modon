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
        Schema::create('mission_inspection_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('inspection_type_id')->constrained('inspection_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('mission_inspection_type');
    }
};
