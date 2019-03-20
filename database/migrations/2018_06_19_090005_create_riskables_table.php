<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRiskablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('riskables', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('risk_event_id');

            $table->unsignedInteger('riskable_id');

            $table->enum(
                'riskable_type',
                config('selectOptions.riskables.riskable_type')
            );

            $table->index([ 'riskable_type', 'riskable_id' ]);

            $table
                ->foreign('risk_event_id')
                ->references('id')
                ->on('risk_events')
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
        Schema::dropIfExists('riskables');
    }
}
