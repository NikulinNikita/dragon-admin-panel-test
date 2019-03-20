<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaccaratBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baccarat_bets', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_session_id');
            $table->unsignedInteger('user_till_id');
            $table->unsignedInteger('baccarat_round_id');
            $table->unsignedInteger('baccarat_result_id');
            $table->decimal('amount', 13, 2);
            $table->decimal('default_amount', 13, 2);
            $table->decimal('outcome', 13, 2)->nullable();
            $table->decimal('default_outcome', 13, 2)->nullable();
            $table->decimal('bets_bank_amount', 13, 2)->nullable();
            $table->decimal('bets_bank_default_amount', 13, 2)->nullable();
            $table->decimal('bonus_wager_amount', 13, 2)->nullable();
            $table->decimal('bonus_wager_default_amount', 13, 2)->nullable();
            $table->boolean('has_opposite_bets')->nullable();
            $table->enum('status', config('selectOptions.baccarat_bets.status'));
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timestamp('processed_at')->nullable();

            $table
                ->foreign('user_session_id')
                ->references('id')
                ->on('user_sessions')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('baccarat_round_id')
                ->references('id')
                ->on('baccarat_rounds')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('baccarat_result_id')
                ->references('id')
                ->on('baccarat_results')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('user_till_id')
                ->references('id')
                ->on('user_tills')
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
        Schema::dropIfExists('baccarat_bets');
    }
}
