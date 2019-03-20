<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('staff_id')->unsigned()->nullable();
            $table->integer('user_till_id')->unsigned();
            $table->decimal('amount', 13, 2);
            $table->decimal('default_amount', 13, 2)->nullable();
            $table->decimal('balance', 13, 2)->nullable();
            $table->decimal('default_balance', 13, 2)->nullable();
            $table->enum('operatable_type', config('selectOptions.operations.operatable_type'))->nullable();
            $table->integer('operatable_id')->nullable();
            $table->timestamps();

            $table->index([ 'operatable_type', 'operatable_id' ]);

            $table->foreign('staff_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('user_till_id')->references('id')->on('user_tills')->onUpdate('cascade')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operations');
    }
}
