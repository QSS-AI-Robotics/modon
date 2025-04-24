<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable(); // e.g. mission_created, report_uploaded
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Optional: created by
            $table->json('audience')->nullable(); // e.g. ["region_manager", "modon_admin"]
            $table->json('region_ids')->nullable(); // e.g. [1, 2]
            $table->json('user_ids')->nullable(); // Optional: directly notify specific users
            $table->timestamp('expires_at')->nullable(); // Optional expiration
            $table->boolean('is_global')->default(false); // For all users
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
