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
        Schema::create('drones', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->string('sr_no')->unique(); // serial number
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // links to users table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drones');
    }
};
