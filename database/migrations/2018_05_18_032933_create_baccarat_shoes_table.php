<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaccaratShoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baccarat_shoes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('staff_id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('excluding_card_id');
            $table->enum('status', config('selectOptions.baccarat_shoes.status'));
            $table->json('options')->nullable();
            $table->timestamps();
            $table->timestamp('closed_at')->nullable();

            $table
                ->foreign('staff_id')
                ->references('id')
                ->on('staff')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('table_id')
                ->references('id')
                ->on('tables')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table
                ->foreign('excluding_card_id')
                ->references('id')
                ->on('baccarat_cards')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('baccarat_shoes');
    }
}
