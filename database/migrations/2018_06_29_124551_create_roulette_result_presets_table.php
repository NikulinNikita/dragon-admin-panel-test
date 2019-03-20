<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteResultPresetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_result_presets', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('roulette_result_id');

            $table->unsignedSmallInteger('index');

            $table->enum('status', config('selectOptions.common.status'));

            $table->json('options')->nullable();

            $table
                ->foreign('roulette_result_id')
                ->references('id')
                ->on('roulette_results')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roulette_result_presets');
    }
}
