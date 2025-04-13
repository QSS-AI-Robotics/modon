<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->dropForeign(['approved_by_city_manager']);
            $table->dropForeign(['approved_by_region_manager']);
            $table->dropForeign(['approved_by_modon_admin']);
            $table->dropColumn([
                'approved_by_city_manager',
                'approved_by_region_manager',
                'approved_by_modon_admin'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->foreignId('approved_by_city_manager')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_region_manager')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_modon_admin')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
