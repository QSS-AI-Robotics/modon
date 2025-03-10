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
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'region_id')) { // ✅ Check if 'region_id' is missing
                $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            }
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
};
