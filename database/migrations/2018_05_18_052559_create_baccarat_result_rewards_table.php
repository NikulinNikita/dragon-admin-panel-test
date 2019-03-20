<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaccaratResultRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baccarat_result_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('baccarat_result_id');
            $table->char('code', 3)->nullable();
            $table->decimal('coefficient', 4, 2);
            $table->json('options')->nullable();

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
        Schema::dropIfExists('baccarat_result_rewards');
    }
}
