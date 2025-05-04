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
        Schema::table('user_types', function (Blueprint $table) {
            $table->integer('hierarchy_level')->default(0)->after('name');
        });
    }
    
    public function down()
    {
        Schema::table('user_types', function (Blueprint $table) {
            $table->dropColumn('hierarchy_level');
        });
    }
};
