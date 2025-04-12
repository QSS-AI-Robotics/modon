<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePilotsTable extends Migration
{
    public function up()
    {
        Schema::create('pilots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // link to users
            $table->string('license_no');
            $table->date('license_expiry');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pilots');
    }
}
