<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteTrackSectionsResultPresetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_track_sections_result_presets', function (Blueprint $table) {
            $table->unsignedInteger('roulette_track_section_id');
            $table->unsignedInteger('roulette_result_preset_id');

            $table->primary(
                [ 'roulette_track_section_id', 'roulette_result_preset_id' ],
                'composite_primary'
            );

            $table
                ->foreign(
                    'roulette_track_section_id',
                    'fk_roulette_track_sections'
                )
                ->references('id')
                ->on('roulette_track_sections')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table
                ->foreign(
                    'roulette_result_preset_id',
                    'fk_roulette_result_presets'
                )
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
        Schema::dropIfExists('roulette_track_sections_result_presets');
    }
}
