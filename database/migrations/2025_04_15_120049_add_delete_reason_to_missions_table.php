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
            $table->text('delete_reason')->nullable()->after('pilot_id');
        });
    }
    
    public function down()
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('delete_reason');
        });
    }
};
