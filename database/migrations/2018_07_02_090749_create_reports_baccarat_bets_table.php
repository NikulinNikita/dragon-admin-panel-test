<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsBaccaratBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_baccarat_bets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_session_id');
            $table->unsignedInteger('baccarat_round_id');
            $table->unsignedInteger('baccarat_result_id');
            $table->decimal('player', 13, 2)->nullable();
            $table->decimal('banker', 13, 2)->nullable();
            $table->decimal('tie', 13, 2)->nullable();
            $table->decimal('player-pair', 13, 2)->nullable();
            $table->decimal('banker-pair', 13, 2)->nullable();
            $table->decimal('big', 13, 2)->nullable();
            $table->decimal('small', 13, 2)->nullable();
            $table->decimal('player-dragon', 13, 2)->nullable();
            $table->decimal('banker-dragon', 13, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('default_amount', 13, 2);
            $table->decimal('outcome', 13, 2)->nullable();
            $table->decimal('default_outcome', 13, 2)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_session_id')->references('id')->on('user_sessions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('baccarat_round_id')->references('id')->on('baccarat_rounds')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('baccarat_result_id')->references('id')->on('baccarat_results')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports_baccarat_bets');
    }
}
