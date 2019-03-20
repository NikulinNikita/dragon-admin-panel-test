<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentRewardsTable extends Migration
{
    public function up()
    {
        Schema::create('agent_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->decimal('amount', 13, 2)->unsigned();
            $table->decimal('default_amount', 13, 2)->unsigned();
            $table->decimal('balance', 13, 2)->unsigned()->nullable();
            $table->decimal('default_balance', 13, 2)->unsigned()->nullable();
            $table->enum('type', config('selectOptions.agent_rewards.type'));
            $table->enum('status', config('selectOptions.agent_rewards.status'));
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_rewards');
    }
}
