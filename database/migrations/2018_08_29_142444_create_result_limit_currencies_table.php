<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultLimitCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_limit_currencies', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('table_limit_currency_id');

            $table->enum(
                'limitable_type',
                config('selectOptions.result_limit_currencies.limitable_type')
            );

            $table->unsignedInteger('limitable_id')->default(0);

            $table->unsignedInteger('min_limit');
            $table->unsignedInteger('max_limit');

            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();

            $table->timestamps();

            $table->index([ 'limitable_type', 'limitable_id' ]);

            $table
                ->foreign('table_limit_currency_id')
                ->references('id')
                ->on('table_limit_currencies')
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
        Schema::dropIfExists('result_limit_currencies');
    }
}
