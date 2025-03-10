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
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert default user types
        DB::table('user_types')->insert([
            ['name' => 'qss_admin'],
            ['name' => 'modon_admin'],
            ['name' => 'region_manager'],
            ['name' => 'pilot']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_types');
    }
};
