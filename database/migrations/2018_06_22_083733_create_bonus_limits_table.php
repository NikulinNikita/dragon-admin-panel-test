<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusLimitsTable extends Migration
{
    public function up()
    {
        Schema::create('bonus_limits', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['max_bonus', 'min_deposit', 'deposit_bonus_min_net_loss', 'default_amount']);
            $table->integer('bonus_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->decimal('value', 13, 2)->unsigned();

            $table->timestamps();

            $table->unique(['type', 'bonus_id', 'currency_id']);
            $table
                ->foreign('bonus_id')
                ->references('id')
                ->on('bonuses')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
        Schema::dropIfExists('bonus_limits');
    }
}
