<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBonusLimitsTable extends Migration
{
    public function up()
    {
        Schema::create('user_bonus_limits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bonus_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->decimal('value', 13, 2)->unsigned();

            $table->timestamps();

            $table->unique(['bonus_id', 'user_id']);
            $table
                ->foreign('bonus_id')
                ->references('id')
                ->on('bonuses')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_bonus_limits');
    }
}
