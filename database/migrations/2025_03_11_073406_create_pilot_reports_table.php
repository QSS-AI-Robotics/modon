<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pilot_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_reference')->unique();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->timestamp('start_datetime')->useCurrent();
            $table->timestamp('end_datetime')->useCurrent();
            $table->string('video_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ✅ Table for multiple images linked to reports
        Schema::create('pilot_report_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilot_report_id')->constrained('pilot_reports')->onDelete('cascade');
            $table->foreignId('inspection_type_id')->constrained('inspection_types')->onDelete('cascade'); // ✅ Foreign key for inspection type
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade'); // ✅ Foreign key for location
            $table->text('description')->nullable(); // ✅ Store description per image
            $table->string('image_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('pilot_report_images');
        Schema::dropIfExists('pilot_reports');
    }
};

