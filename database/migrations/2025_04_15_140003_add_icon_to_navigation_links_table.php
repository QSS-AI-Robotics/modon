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
        Schema::table('navigation_links', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name'); // or after any column you prefer
        });
    }
    
    public function down()
    {
        Schema::table('navigation_links', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
