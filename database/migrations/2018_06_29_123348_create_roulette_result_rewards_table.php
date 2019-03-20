<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteResultRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_result_rewards', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('roulette_result_id');
            $table->decimal('coefficient', 4, 2);
            $table->json('options')->nullable();

            $table
                ->foreign('roulette_result_id')
                ->references('id')
                ->on('roulette_results')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roulette_result_rewards');
    }
}
