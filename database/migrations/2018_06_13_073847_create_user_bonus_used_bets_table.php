<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBonusUsedBetsTable extends Migration
{
    public function up()
    {
        Schema::create('user_bonus_used_bets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_bonus_id')->unsigned();
            $table->enum('bet_type', config('selectOptions.bet_types'));
            $table->integer('bet_id')->default(0);

            $table->foreign('user_bonus_id')->references('id')->on('user_bonuses')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_bonus_used_bets');
    }
}
