<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->boolean('city_manager_approved')->default(false);
            $table->boolean('region_manager_approved')->default(false);
            $table->boolean('modon_admin_approved')->default(false);
            $table->boolean('is_fully_approved')->default(false);

            // Optional approver user IDs
            $table->foreignId('approved_by_city_manager')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_region_manager')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_modon_admin')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->dropColumn([
                'city_manager_approved',
                'region_manager_approved',
                'modon_admin_approved',
                'is_fully_approved',
                'approved_by_city_manager',
                'approved_by_region_manager',
                'approved_by_modon_admin'
            ]);
        });
    }
};
