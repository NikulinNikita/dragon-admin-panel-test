<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tills', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('till_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('till_id')->unsigned();

            $table->string('title', 191);
            $table->text('description');

            $table->string('locale')->index();
            $table->unique(['till_id', 'locale']);
            $table->foreign('till_id')->references('id')->on('tills')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('till_translations');
        Schema::dropIfExists('tills');
    }
}
