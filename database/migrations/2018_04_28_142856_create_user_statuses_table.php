<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 191);
            $table->decimal('multiplier', 3, 1)->unsigned();
            $table->integer('duration')->unsigned();
            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('user_status_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_status_id')->unsigned();

            $table->string('title');
            $table->text('description');

            $table->string('locale')->index();
            $table->unique(['user_status_id', 'locale']);
            $table->foreign('user_status_id')->references('id')->on('user_statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_status_translations');
        Schema::dropIfExists('user_statuses');
    }
}
