<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->string('name', 191);
            $table->string('number', 191);
            $table->decimal('fee', 4, 2);
            $table->integer('min_limit');
            $table->integer('max_limit');
            $table->boolean('visible_for_users')->default(false);
            $table->text('notes');
            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('bank_id')->references('id')->on('banks')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('bank_account_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_account_id')->unsigned();

            $table->text('description')->nullable();

            $table->string('locale')->index();
            $table->unique(['bank_account_id', 'locale']);
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_account_translations');
        Schema::dropIfExists('bank_accounts');
    }
}
