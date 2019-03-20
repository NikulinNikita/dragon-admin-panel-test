<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteRoundResultPresetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_round_result_presets', function (Blueprint $table) {
            $table->unsignedInteger('roulette_round_id');
            $table->unsignedInteger('roulette_result_preset_id');

            $table->primary(
                [ 'roulette_round_id', 'roulette_result_preset_id' ],
                'composite_primary'
            );

            $table
                ->foreign('roulette_round_id')
                ->references('id')
                ->on('roulette_rounds')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table
                ->foreign('roulette_result_preset_id')
                ->references('id')
                ->on('roulette_result_presets')
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
        Schema::dropIfExists('roulette_round_result_presets');
    }
}
