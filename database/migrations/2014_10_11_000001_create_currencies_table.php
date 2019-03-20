<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->char('code', 3)->unique();
            $table->string('symbol', 10);
            $table->enum('status', config('selectOptions.common.status'));
            $table->tinyInteger('fmt_decimals', false, true)->default(0);
            $table->enum('fmt_dec_point', config('selectOptions.currencies.delimiter'));
            $table->enum('fmt_thousands_sep', config('selectOptions.currencies.delimiter'));
            $table->enum('fmt_symbol_placement', config('selectOptions.currencies.fmt_symbol_placement'))->default('after');
            $table->json('options')->nullable();
        });

        Schema::create('currency_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('currency_id')->unsigned();

            $table->string('title', 100);

            $table->string('locale')->index();
            $table->unique(['currency_id', 'locale']);
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_translations');
        Schema::dropIfExists('currencies');
    }
}
