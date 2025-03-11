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
            $table->string('status')->default('Pending'); // ✅ Default to "Pending"
            $table->boolean('report_submitted')->default(0); // ✅ Default to 0 (false)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('report_submitted');
        });
    }
};

