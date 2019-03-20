<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaccaratCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baccarat_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->char('code', 2);
            $table->string('title', 45);
            $table->tinyInteger('value')->unsigned();
            $table->json('options')->nullable();

            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('baccarat_cards');
    }
}
