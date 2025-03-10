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
        Schema::table('missions', function (Blueprint $table) {
            if (!Schema::hasColumn('missions', 'inspection_type_id')) { // ✅ Prevent duplicate column error
                $table->foreignId('inspection_type_id')->after('id')->constrained('inspection_types')->onDelete('cascade');
            }
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            //
        });
    }
};
