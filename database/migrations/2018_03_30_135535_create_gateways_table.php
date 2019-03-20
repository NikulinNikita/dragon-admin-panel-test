<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateways', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 191);
            $table->enum('status', config('selectOptions.common.status'));
            $table->tinyInteger('enabled_for_deposit')->unsigned()->default(1);
            $table->tinyInteger('enabled_for_withdrawal')->unsigned()->default(1);
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index('enabled_for_deposit');
            $table->index('enabled_for_withdrawal');
        });

        Schema::create('gateway_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('gateway_id')->unsigned();

            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->text('duration')->nullable();

            $table->string('locale')->index();
            $table->unique(['gateway_id', 'locale']);
            $table->foreign('gateway_id')->references('id')->on('gateways')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gateway_translations');
        Schema::dropIfExists('gateways');
    }
}
