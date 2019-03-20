<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsUserFinancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports_user_finances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('currency_id');

            $table->decimal('balance_before', 13, 2)->nullable();
            $table->decimal('default_balance_before', 13, 2)->nullable();
            $table->decimal('bonuses_balance_before', 13, 2)->nullable();
            $table->decimal('bonuses_default_balance_before', 13, 2)->nullable();
            $table->decimal('partners_balance_before', 13, 2)->nullable();
            $table->decimal('partners_default_balance_before', 13, 2)->nullable();

            $table->decimal('deposits_amount_before', 13, 2)->nullable();
            $table->decimal('deposits_default_amount_before', 13, 2)->nullable();
            $table->decimal('withdrawals_amount_before', 13, 2)->nullable();
            $table->decimal('withdrawals_default_amount_before', 13, 2)->nullable();

            $table->decimal('deposits_amount', 13, 2)->nullable();
            $table->decimal('deposits_default_amount', 13, 2)->nullable();
            $table->decimal('withdrawals_amount', 13, 2)->nullable();
            $table->decimal('withdrawals_default_amount', 13, 2)->nullable();

            $table->decimal('baccarat_bets_amount', 13, 2)->nullable();
            $table->decimal('baccarat_bets_default_amount', 13, 2)->nullable();
            $table->decimal('roulette_bets_amount', 13, 2)->nullable();
            $table->decimal('roulette_bets_default_amount', 13, 2)->nullable();
            $table->decimal('baccarat_bets_outcome', 13, 2)->nullable();
            $table->decimal('baccarat_bets_default_outcome', 13, 2)->nullable();
            $table->decimal('roulette_bets_outcome', 13, 2)->nullable();
            $table->decimal('roulette_bets_default_outcome', 13, 2)->nullable();
            $table->decimal('baccarat_bets_losts_amount', 13, 2)->nullable();
            $table->decimal('baccarat_bets_losts_default_amount', 13, 2)->nullable();
            $table->decimal('roulette_bets_losts_amount', 13, 2)->nullable();
            $table->decimal('roulette_bets_losts_default_amount', 13, 2)->nullable();

            $table->decimal('bonuses_amount', 13, 2)->nullable();
            $table->decimal('bonuses_default_amount', 13, 2)->nullable();
            $table->decimal('partners_amount', 13, 2)->nullable();
            $table->decimal('partners_default_amount', 13, 2)->nullable();
            $table->decimal('used_bonuses_amount', 13, 2)->nullable();
            $table->decimal('used_bonuses_default_amount', 13, 2)->nullable();
            $table->decimal('canceled_bonuses_amount', 13, 2)->nullable();
            $table->decimal('canceled_bonuses_default_amount', 13, 2)->nullable();
            $table->decimal('used_partners_amount', 13, 2)->nullable();
            $table->decimal('used_partners_default_amount', 13, 2)->nullable();
            $table->decimal('canceled_partners_amount', 13, 2)->nullable();
            $table->decimal('canceled_partners_default_amount', 13, 2)->nullable();

            $table->decimal('balance_after', 13, 2)->nullable();
            $table->decimal('default_balance_after', 13, 2)->nullable();
            $table->decimal('bonuses_balance_after', 13, 2)->nullable();
            $table->decimal('bonuses_default_balance_after', 13, 2)->nullable();
            $table->decimal('partners_balance_after', 13, 2)->nullable();
            $table->decimal('partners_default_balance_after', 13, 2)->nullable();

            $table->decimal('bets_wins_alteration', 13, 2)->nullable();
            $table->decimal('bets_wins_default_alteration', 13, 2)->nullable();
            $table->decimal('balance_alteration', 13, 2)->nullable();
            $table->decimal('default_balance_alteration', 13, 2)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports_user_finances');
    }
}
