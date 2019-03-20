<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRiskEventStaffActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_event_staff_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('risk_event_id');
            $table->unsignedInteger('staff_id');
            $table->text('message');
            $table->json('options')->nullable();
            $table->timestamps();
            $table->enum('assigned_status', config('selectOptions.risk_event_staff_actions.status'));

            $table
                ->foreign('risk_event_id')
                ->references('id')
                ->on('risk_events')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('staff_id')
                ->references('id')
                ->on('staff')
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
        Schema::dropIfExists('risk_event_staff_actions');
    }
}
