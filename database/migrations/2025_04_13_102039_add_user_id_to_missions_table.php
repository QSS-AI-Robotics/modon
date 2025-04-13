<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
        });

        // ✅ Set a valid user_id for all existing missions
        // Example: Get the first admin or fallback to user ID 1
        $defaultUserId = DB::table('users')->first()?->id ?? 1;

        DB::table('missions')->update(['user_id' => $defaultUserId]);

        // ✅ Now apply foreign key constraint
        Schema::table('missions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

