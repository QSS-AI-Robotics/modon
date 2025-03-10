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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_type_id')->constrained('user_types')->onDelete('cascade');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropForeign(['region_id']);
            $table->dropColumn(['user_type_id', 'region_id']);
        });
    }
};
