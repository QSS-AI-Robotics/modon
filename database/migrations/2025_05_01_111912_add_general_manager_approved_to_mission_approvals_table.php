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
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->boolean('general_manager_approved')->default(false)->after('region_manager_approved');
        });
    }
    
    public function down()
    {
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->dropColumn('general_manager_approved');
        });
    }
};
