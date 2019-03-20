<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusesTable extends Migration
{
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 45)->unique();
            $table->enum('target_till', config('selectOptions.bonuses.target_till'));
            $table->tinyInteger('wager')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_number_of_applies')->unsigned()->nullable();
            $table->smallInteger('number_of_applies')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_game_type')->unsigned()->nullable();
            $table->string('game_types', 50)->nullable();

            $table->tinyInteger('limitation_by_player_status')->unsigned()->nullable();
            $table->string('player_statuses', 50)->nullable();

            $table->tinyInteger('limitation_by_identity_approval')->unsigned()->nullable();
            $table->tinyInteger('identity_approved_only')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_documents_submission_period')->unsigned()->nullable();
            $table->smallInteger('documents_submission_period')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_game_duration')->unsigned()->nullable();
            $table->smallInteger('game_duration')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_min_deposit_amount')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_max_bonus_amount')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_wagering_period')->unsigned()->nullable();
            $table->smallInteger('wagering_period')->unsigned()->nullable();

            $table->tinyInteger('limitation_by_wagering_game_type')->unsigned()->nullable();
            $table->string('wagering_game_types', 50)->nullable();

            $table->tinyInteger('limitation_by_bonus_usage')->unsigned()->nullable();

            $table->json('options')->nullable();
        });

        Schema::create('bonus_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bonus_id')->unsigned();

            $table->text('title')->nullable();
            $table->text('description')->nullable();

            $table->string('locale')->index();
            $table->unique(['bonus_id', 'locale']);
            $table->foreign('bonus_id')->references('id')->on('bonuses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_translations');
        Schema::dropIfExists('bonuses');
    }
}
