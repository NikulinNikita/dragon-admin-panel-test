<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalOperationsTable extends Migration
{
    public function up()
    {
        Schema::create('internal_operations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_id');
            $table->unsignedInteger('from_bank_account_id')->nullable();
            $table->unsignedInteger('to_bank_account_id')->nullable();
            $table->unsignedInteger('currency_id');
            $table->decimal('amount', 13, 2)->nullable();
            $table->decimal('default_amount', 13, 2)->nullable();
            $table->enum('type', config('selectOptions.internal_operations.type'));
            $table->text('comment');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('from_bank_account_id')->references('id')->on('bank_accounts')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('to_bank_account_id')->references('id')->on('bank_accounts')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('RESTRICT');
        });
    }

    public function down()
    {
        Schema::dropIfExists('internal_operations');
    }
}
