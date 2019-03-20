<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoopCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loop_commands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 191);
            $table->timestamps();
        });

        Schema::create('loop_command_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('loop_command_id')->unsigned();

			$table->text('description')->nullable();

			$table->string('locale')->index();
			$table->unique(['loop_command_id','locale']);
			$table->foreign('loop_command_id')->references('id')->on('loop_commands')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loop_command_translations');
        Schema::dropIfExists('loop_commands');
    }
}
