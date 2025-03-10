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
            if (Schema::hasColumn('missions', 'inspection_type_id')) {
                $table->dropForeign(['inspection_type_id']);
                $table->dropColumn('inspection_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->foreignId('inspection_type_id')->nullable()->constrained('inspection_types')->onDelete('cascade');
        });
    }
};
