<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaccaratRoundResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baccarat_round_results', function (Blueprint $table) {
            $table->unsignedInteger('baccarat_round_id');
            $table->unsignedInteger('baccarat_result_id');

            $table->primary(
                [ 'baccarat_round_id', 'baccarat_result_id' ],
                'composite_primary'
            );

            $table
                ->foreign('baccarat_round_id')
                ->references('id')
                ->on('baccarat_rounds')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table
                ->foreign('baccarat_result_id')
                ->references('id')
                ->on('baccarat_results')
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
        Schema::dropIfExists('baccarat_round_results');
    }
}
