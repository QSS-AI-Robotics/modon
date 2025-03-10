<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert default regions
        DB::table('regions')->insert([
            ['name' => 'all'],
            ['name' => 'central'],
            ['name' => 'western'],
            ['name' => 'eastern']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('regions');
    }
};
