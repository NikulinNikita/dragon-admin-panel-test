<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentRewardLimitsTable extends Migration
{
    public function up()
    {
        Schema::create('agent_reward_limits', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['min', 'max_automatic_processing_limit']);
            $table->integer('currency_id')->unsigned();
            $table->decimal('value', 13, 2)->unsigned();

            $table->timestamps();

            $table->unique(['type', 'currency_id']);
            $table
                ->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_reward_limits');
    }
}
