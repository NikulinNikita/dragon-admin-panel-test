<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('gateway_id');
            $table->string('slug', 191);
            $table->text('notes');
            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('gateway_id')->references('id')->on('gateways')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('bank_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank_id')->unsigned();

            $table->string('title', 191);
            $table->string('address', 191);
            $table->text('description')->nullable();

            $table->string('locale')->index();
            $table->unique(['bank_id', 'locale']);
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_translations');
        Schema::dropIfExists('banks');
    }
}
