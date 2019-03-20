<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouletteResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roulette_results', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 45);

            $table->enum(
                'status',
                config('selectOptions.common.status')
            );

            $table->json('options')->nullable();

            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roulette_results');
    }
}
