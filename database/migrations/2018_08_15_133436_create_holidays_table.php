<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidaysTable extends Migration
{
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->increments('id');

            $table->tinyInteger('is_global')->unsigned();
            $table->tinyInteger('is_recurring')->unsigned()->default(1);

            $table->tinyInteger('day')->unsigned();
            $table->tinyInteger('month')->unsigned();
            $table->smallInteger('year')->unsigned()->nullable();

            $table->tinyInteger('wager')->unsigned()->nullable();
            $table->tinyInteger('is_usage_limited')->unsigned()->default(1);

            $table->timestamps();

            $table->index('is_global', 'GLOBAL');
            $table->index('is_recurring', 'RECURRING');
            $table->index(['day', 'month', 'year'], 'DATE');
        });

        Schema::create('holiday_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('holiday_id')->unsigned();

            $table->text('title')->nullable();
            $table->text('description')->nullable();

            $table->string('locale')->index();
            $table->unique(['holiday_id', 'locale']);
            $table->foreign('holiday_id')->references('id')->on('holidays')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('holiday_translations');
        Schema::dropIfExists('holidays');
    }
}
