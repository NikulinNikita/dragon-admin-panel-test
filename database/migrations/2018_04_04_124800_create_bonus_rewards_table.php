<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusRewardsTable extends Migration
{
    public function up()
    {
        Schema::create('bonus_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('status', ['pending', 'active', 'paid', 'canceled']);
            $table->integer('user_id')->unsigned();
            $table->integer('bonus_id')->unsigned();
            $table->decimal('amount', 13, 2)->unsigned();
            $table->timestamps();
            $table->json('options')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('bonus_id')->references('id')->on('bonuses')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_rewards');
    }
}
