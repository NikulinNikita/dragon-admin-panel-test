<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteResultPresetCellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_result_preset_cells', function (Blueprint $table) {
            $table->unsignedInteger('roulette_result_preset_id');
            $table->unsignedInteger('roulette_cell_id');

            $table->primary(
                [ 'roulette_result_preset_id', 'roulette_cell_id' ],
                'composite_primary'
            );

            $table
                ->foreign('roulette_result_preset_id')
                ->references('id')
                ->on('roulette_result_presets')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table
                ->foreign('roulette_cell_id')
                ->references('id')
                ->on('roulette_cells')
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
        Schema::dropIfExists('roulette_result_preset_cells');
    }
}
