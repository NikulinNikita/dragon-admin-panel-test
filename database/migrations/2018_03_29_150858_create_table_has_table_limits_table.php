<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHasTableLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_has_table_limit', function (Blueprint $table) {
            $table->integer('table_id')->unsigned();
            $table->integer('table_limit_id')->unsigned();

            $table->foreign('table_id')->references('id')->on('tables')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('table_limit_id')->references('id')->on('table_limits')->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['table_id', 'table_limit_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_has_table_limit');
    }
}
