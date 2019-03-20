<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentRewardBetsTable extends Migration
{
    public function up()
    {
        Schema::create('agent_reward_bets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agent_reward_id')->unsigned();
            $table->integer('player_agent_id')->unsigned();
            $table->integer('player_user_id')->unsigned();
            $table->integer('agent_agent_id')->unsigned();
            $table->integer('agent_user_id')->unsigned();
            $table->enum('bet_type', ['baccarat_bet', 'roulette_bet']);
            $table->integer('bet_id')->default(0);
            $table->enum('bet_result', ['won', 'lost', 'stay']);
            $table->decimal('bet_amount', 13, 2)->unsigned();
            $table->decimal('default_bet_amount', 13, 2)->unsigned();
            $table->tinyInteger('subagent_level_distance')->unsigned();
            $table->decimal('default_bet_bank_amount', 13, 2)->unsigned();
            $table->float('level_percent', 3, 2)->unsigned();
            $table->decimal('default_reward_amount', 13, 2)->unsigned();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();


            $table->foreign('player_agent_id')->references('id')->on('agents')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('agent_agent_id')->references('id')->on('agents')->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('player_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('agent_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('agent_reward_id')->references('id')->on('agent_rewards')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_reward_bets');
    }
}
