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
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('rejected_by')->nullable()->after('modon_admin_approved');
            $table->text('rejection_note')->nullable()->after('rejected_by');
    
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
        });
    }
    
    public function down()
    {
        Schema::table('mission_approvals', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['rejected_by', 'rejection_note']);
        });
    }
};
