<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->unsigned();
            $table->string('slug', 191);
            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();
            $table->timestamps();
            $table->integer('order')->unsigned();

            $table->foreign('game_id')->references('id')->on('games')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('table_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id')->unsigned();

            $table->string('title', 191);
            $table->text('description')->nullable();

            $table->string('locale')->index();
            $table->unique(['table_id','locale']);
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_translations');
        Schema::dropIfExists('tables');
    }
}
