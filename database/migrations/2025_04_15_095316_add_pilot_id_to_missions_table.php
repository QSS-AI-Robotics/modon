<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->foreignId('pilot_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
    
    public function down()
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropForeign(['pilot_id']);
            $table->dropColumn('pilot_id');
        });
    }
};
