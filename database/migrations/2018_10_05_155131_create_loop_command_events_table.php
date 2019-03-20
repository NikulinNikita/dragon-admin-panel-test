<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoopCommandEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loop_command_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id')->unsigned();
            $table->integer('staff_session_id')->unsigned();
            $table->morphs('roundable');
            $table->timestamps();
            $table->enum('status', config('selectOptions.loop_command_events.status'));
            $table->integer('loop_command_id')->unsigned();
            $table->integer('staff_id')->nullable()->unsigned();            

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            //$table->foreign('staff_session_id')->references('id')->on('staff_sessions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('loop_command_id')->references('id')->on('loop_commands')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loop_command_events');
    }
}
