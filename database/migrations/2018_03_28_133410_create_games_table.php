<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	public function up()
	{
		Schema::create('games', function (Blueprint $table) {
			$table->increments('id');
			$table->string('slug', 191);
			$table->enum('status', config('selectOptions.common.status'));
			$table->json('options')->nullable();
			$table->timestamps();
			$table->integer('order')->unsigned();
		});

		Schema::create('game_translations', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('game_id')->unsigned();

			$table->string('title', 191);
			$table->text('description')->nullable();

			$table->string('locale')->index();
			$table->unique(['game_id','locale']);
			$table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('game_translations');
		Schema::dropIfExists('games');
	}
}
