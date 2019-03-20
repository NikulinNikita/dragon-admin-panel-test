<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsDealerShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_dealer_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_session_id');
            $table->unsignedInteger('table_id');
            $table->integer('users_count')->nullable();
            $table->integer('bets_count')->nullable();
            $table->decimal('bets_amount', 13, 2)->nullable();
            $table->decimal('bets_outcome', 13, 2)->nullable();
            $table->decimal('balance', 13, 2)->nullable();
            $table->mediumInteger('profitability')->nullable();
            $table->string('manager', 191)->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('shift_created_at')->nullable();
            $table->timestamp('shift_ended_at')->nullable();
            $table->timestamps();

            $table->foreign('staff_session_id')->references('id')->on('staff_sessions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports_dealer_shifts');
    }
}
