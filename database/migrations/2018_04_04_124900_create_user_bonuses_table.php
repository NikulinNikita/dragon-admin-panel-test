<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBonusesTable extends Migration
{
    public function up()
    {
        Schema::create('user_bonuses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('bonus_id')->unsigned();
            $table->integer('bonus_reward_id')->unsigned()->nullable();
            $table->decimal('amount', 13, 2)->unsigned();
            $table->decimal('default_amount', 13, 2)->unsigned();
            $table->enum('status', config('selectOptions.user_bonuses.status'));
            $table->string('state', 80)->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timestamp('betting_period_start')->nullable();
            $table->timestamp('betting_period_end')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('bonus_id')->references('id')->on('bonuses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('bonus_reward_id')->references('id')->on('bonus_rewards')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_bonuses');
    }
}
