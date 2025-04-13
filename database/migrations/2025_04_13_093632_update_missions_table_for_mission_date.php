<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['start_datetime', 'end_datetime']); // 👈 remove old
            $table->date('mission_date')->after('note')->nullable(); // 👈 add new
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->dropColumn('mission_date');
        });
    }
};

