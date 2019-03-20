<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteCellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_cells', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('value');
            $table->enum('color', config('selectOptions.roulette_cells.color'));

            $table->json('options')->nullable();

            $table->unique('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roulette_cells');
    }
}
