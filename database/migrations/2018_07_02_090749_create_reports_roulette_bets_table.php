<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsRouletteBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_roulette_bets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_session_id');
            $table->unsignedInteger('roulette_round_id');
            $table->unsignedInteger('roulette_result_id');
            $table->unsignedInteger('roulette_result_preset_id');
            $table->decimal('split', 13, 2)->nullable();
            $table->decimal('street', 13, 2)->nullable();
            $table->decimal('corner', 13, 2)->nullable();
            $table->decimal('six-line', 13, 2)->nullable();
            $table->decimal('first-four', 13, 2)->nullable();
            $table->decimal('red', 13, 2)->nullable();
            $table->decimal('black', 13, 2)->nullable();
            $table->decimal('odd', 13, 2)->nullable();
            $table->decimal('even', 13, 2)->nullable();
            $table->decimal('low', 13, 2)->nullable();
            $table->decimal('high', 13, 2)->nullable();
            $table->decimal('column1', 13, 2)->nullable();
            $table->decimal('column2', 13, 2)->nullable();
            $table->decimal('column3', 13, 2)->nullable();
            $table->decimal('dozen1', 13, 2)->nullable();
            $table->decimal('dozen2', 13, 2)->nullable();
            $table->decimal('dozen3', 13, 2)->nullable();
            $table->text('bet_nominals')->nullable();
            $table->text('bet_numbers')->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('default_amount', 13, 2);
            $table->decimal('outcome', 13, 2)->nullable();
            $table->decimal('default_outcome', 13, 2)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_session_id')->references('id')->on('user_sessions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('roulette_round_id')->references('id')->on('roulette_rounds')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('roulette_result_id')->references('id')->on('roulette_results')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('roulette_result_preset_id')->references('id')->on('roulette_result_presets')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports_roulette_bets');
    }
}
