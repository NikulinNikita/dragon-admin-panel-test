<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidayRegionsTable extends Migration
{
    public function up()
    {
        Schema::create('holiday_regions', function (Blueprint $table) {
            $table->integer('holiday_id')->unsigned();
            $table->integer('region_id')->unsigned();

            $table->foreign('holiday_id')->references('id')->on('holidays')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('region_id')->references('id')->on('regions')->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['holiday_id', 'region_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('holiday_regions');
    }
}
