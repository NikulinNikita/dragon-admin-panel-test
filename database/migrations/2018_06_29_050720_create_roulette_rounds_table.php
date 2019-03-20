<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_rounds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_session_id');
            $table->unsignedInteger('roulette_cell_id')->nullable();
            $table->timestamp('bet_acception_started_at')->nullable();
            $table->timestamp('bet_acception_ended_at')->nullable();
            $table->enum('status', config('selectOptions.roulette_rounds.status'));
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timestamp('ended_at')->nullable();

            $table
                ->foreign('staff_session_id')
                ->references('id')
                ->on('staff_sessions')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('roulette_cell_id')
                ->references('id')
                ->on('roulette_cells')
                ->onUpdate('restrict')
                ->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roulette_rounds');
    }
}
