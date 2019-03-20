<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentLinksTable extends Migration
{
    public function up()
    {
        Schema::create('agent_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('title', 50);
            $table->enum('status', config('selectOptions.agent_links.status'));
            $table->integer('registered_user_id')->unsigned()->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('registered_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['user_id', 'title']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_links');
    }
}
