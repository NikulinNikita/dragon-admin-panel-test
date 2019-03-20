<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidayBonusAmountsTable extends Migration
{
    public function up()
    {
        Schema::create('holiday_bonus_amounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('holiday_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->decimal('amount', 13, 2)->unsigned();

            $table->unique(['holiday_id', 'currency_id']);
            $table->foreign('holiday_id')->references('id')->on('holidays')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('holiday_bonus_amounts');
    }
}
