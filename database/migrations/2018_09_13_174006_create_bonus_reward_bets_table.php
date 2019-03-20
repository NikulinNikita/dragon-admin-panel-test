<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusRewardBetsTable extends Migration
{
    public function up()
    {
        Schema::create('bonus_reward_bets', function (Blueprint $table) {
            $table->integer('bonus_reward_id')->unsigned();
            $table->enum('bet_type', ['baccarat_bet', 'roulette_bet']);
            $table->integer('bet_id')->default(0);

            $table->foreign('bonus_reward_id')->references('id')->on('bonus_rewards')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['bonus_reward_id', 'bet_type', 'bet_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_reward_bets');
    }
}
