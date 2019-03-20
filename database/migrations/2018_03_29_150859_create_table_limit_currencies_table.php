<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLimitCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_limit_currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('currency_id')->unsigned();
            $table->integer('table_limit_id')->unsigned();
            $table->integer('min_limit')->unsigned();
            $table->integer('max_limit')->unsigned();
            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('table_limit_id')->references('id')->on('table_limits')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['table_limit_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_limit_currencies');
    }
}
