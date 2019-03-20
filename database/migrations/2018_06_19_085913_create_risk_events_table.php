<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRiskEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_events', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('risk_level_id');
            $table->unsignedInteger('risk_id');
            $table->json('data');
            $table->enum('source', config('selectOptions.risk_events.source'));
            $table->enum('status', config('selectOptions.risk_events.status'));
            $table->json('options')->nullable();
            $table->timestamps();


            $table
                ->foreign('risk_level_id')
                ->references('id')
                ->on('risk_levels')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('risk_id')
                ->references('id')
                ->on('risks')
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
        Schema::dropIfExists('risk_events');
    }
}
