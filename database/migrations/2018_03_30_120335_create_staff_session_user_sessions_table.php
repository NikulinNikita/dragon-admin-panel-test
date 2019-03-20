<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffSessionUserSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_session_user_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_session_id');
            $table->unsignedInteger('user_session_id');
            $table->unsignedInteger('bets')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->timestamps();

            $table->foreign('staff_session_id')->references('id')->on('staff_sessions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_session_id')->references('id')->on('user_sessions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_session_user_sessions');
    }
}
