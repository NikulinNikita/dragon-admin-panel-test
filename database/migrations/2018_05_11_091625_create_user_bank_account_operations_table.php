<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBankAccountOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bank_account_operations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_bank_account_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->decimal('value', 13, 2);
            $table->decimal('default_value', 13, 2)->nullable();
            $table->decimal('balance', 13, 2)->nullable();
            $table->decimal('default_balance', 13, 2)->nullable();
            $table->enum('operatable_type', config('selectOptions.bank_account_operations.operatable_type'));
            $table->integer('operatable_id')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('user_bank_account_id')->references('id')->on('user_bank_accounts')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('user_bank_account_operations');
    }
}
