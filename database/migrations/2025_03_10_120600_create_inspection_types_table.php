<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ✅ Import DB Facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('inspection_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // ✅ Insert predefined inspection types
        DB::table('inspection_types')->insert([
            ['name' => 'Traffic Analysis'],
            ['name' => 'Thermal Anomalies'],
            ['name' => 'Gas Leaks'],
            ['name' => 'Road Cracks'],
            ['name' => 'Road Safety'],
            ['name' => 'Storage Area'],
            ['name' => 'Outdoor Violation'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('inspection_types');
    }
};
