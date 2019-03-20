<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBetsBankAccrualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bets_bank_accruals', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_session_id');

            $table->unsignedInteger('roundable_id')->nullable();
            $table->enum(
                'roundable_type',
                config('selectOptions.bets_bank_accruals.roundable_type')
            );

            $table->decimal('bets_total_amount', 13, 2);
            $table->decimal('bets_total_default_amount', 13, 2);
            $table->decimal('bets_total_outcome', 13, 2);
            $table->decimal('bets_total_default_outcome', 13, 2);
            $table->decimal('bets_total_profit', 13, 2);
            $table->decimal('bets_total_default_profit', 13, 2);
            $table->decimal('bets_bank_total_amount', 13, 2);
            $table->decimal('bets_bank_total_default_amount', 13, 2);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timestamp('used_at')->nullable();

            $table->index([ 'roundable_type', 'roundable_id' ]);

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('user_session_id')
                ->references('id')
                ->on('user_sessions')
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
        Schema::dropIfExists('bets_bank_accruals');
    }
}
