<?php

use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_id')->nullable();
            $table->unsignedInteger('table_id');
            $table->enum('status', config('selectOptions.common.status'));
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timestamp('ended_at')->nullable();

            $table->foreign('staff_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_sessions');
    }
}
