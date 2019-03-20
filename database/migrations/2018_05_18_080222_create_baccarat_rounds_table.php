<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaccaratRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baccarat_rounds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_session_id');
            $table->unsignedInteger('baccarat_shoe_id');
            $table->unsignedSmallInteger('number');
            $table->boolean('is_extra_results_allowed');
            $table->timestamp('bet_acception_started_at')->nullable();
            $table->timestamp('bet_acception_ended_at')->nullable();
            $table->unsignedInteger('player_card_1')->nullable();
            $table->unsignedInteger('player_card_2')->nullable();
            $table->unsignedInteger('player_card_3')->nullable();
            $table->unsignedInteger('banker_card_1')->nullable();
            $table->unsignedInteger('banker_card_2')->nullable();
            $table->unsignedInteger('banker_card_3')->nullable();
            $table->unsignedTinyInteger('player_score')->nullable();
            $table->unsignedTinyInteger('banker_score')->nullable();
            $table->unsignedTinyInteger('player_cards_count')->nullable();
            $table->unsignedTinyInteger('banker_cards_count')->nullable();
            $table->enum('status', config('selectOptions.baccarat_rounds.status'));
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timestamp('ended_at')->nullable();

            $table
                ->foreign('staff_session_id')
                ->references('id')
                ->on('staff_sessions')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('baccarat_shoe_id')
                ->references('id')
                ->on('baccarat_shoes')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('player_card_1')
                ->references('id')
                ->on('baccarat_cards')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('player_card_2')
                ->references('id')
                ->on('baccarat_cards')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('player_card_3')
                ->references('id')
                ->on('baccarat_cards')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('banker_card_1')
                ->references('id')
                ->on('baccarat_cards')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('banker_card_2')
                ->references('id')
                ->on('baccarat_cards')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('banker_card_3')
                ->references('id')
                ->on('baccarat_cards')
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
        Schema::dropIfExists('baccarat_rounds');
    }
}
