<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('full_name', 191)->nullable();
            $table->integer('user_bank_id')->unsigned()->nullable();
            $table->integer('user_bank_account_id')->unsigned()->nullable();
            $table->integer('bank_id')->unsigned()->nullable();
            $table->integer('bank_account_id')->unsigned()->nullable();
            $table->integer('gateway_id')->unsigned()->nullable();
            $table->integer('currency_id')->unsigned()->nullable();
            $table->string('reference', 45);
            $table->string('transaction_ref', 45)->nullable();
            $table->decimal('sent_amount', 13, 2);
            $table->decimal('received_amount', 13, 2)->nullable();
            $table->decimal('received_default_amount', 13, 2)->nullable();
            $table->decimal('total_amount', 13, 2)->nullable();
            $table->decimal('default_amount', 13, 2)->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', config('selectOptions.deposit_requests.status'));
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('user_bank_id')->references('id')->on('user_banks')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('user_bank_account_id')->references('id')->on('user_bank_accounts')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('bank_id')->references('id')->on('banks')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('gateway_id')->references('id')->on('gateways')->onUpdate('cascade')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_requests');
    }
}
