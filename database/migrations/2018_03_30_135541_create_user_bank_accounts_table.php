<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_bank_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->string('number', 191);
            $table->decimal('fee', 4, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', config('selectOptions.user_bank_accounts.status'));
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('user_bank_id')->references('id')->on('user_banks')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('user_bank_accounts');
    }
}
