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
            $table->boolean('pilot_approved')->default(0)->after('modon_admin_approved');
        });
    }
    
    public function down()
    {
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->dropColumn('pilot_approved');
        });
    }
};
