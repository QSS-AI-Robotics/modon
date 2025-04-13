<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mission_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained()->onDelete('cascade');

            $table->boolean('city_manager_approved')->default(false);
            $table->boolean('region_manager_approved')->default(false);
            $table->boolean('modon_admin_approved')->default(false);
            $table->boolean('is_fully_approved')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_approvals');
    }
};
