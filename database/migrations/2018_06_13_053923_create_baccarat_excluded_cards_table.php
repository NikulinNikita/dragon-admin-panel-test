<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaccaratExcludedCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baccarat_excluded_cards', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('baccarat_shoe_id');
            $table->unsignedInteger('baccarat_card_id');

            $table
                ->foreign('baccarat_shoe_id')
                ->references('id')
                ->on('baccarat_shoes')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('baccarat_card_id')
                ->references('id')
                ->on('baccarat_cards')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('baccarat_excluded_cards');
    }
}
