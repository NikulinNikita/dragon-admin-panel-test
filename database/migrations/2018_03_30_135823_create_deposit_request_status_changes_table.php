<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositRequestStatusChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_request_status_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deposit_request_id');
            $table->unsignedInteger('staff_id');
            $table->enum('status', config('selectOptions.deposit_requests.status'));
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('deposit_request_id')->references('id')->on('deposit_requests')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staff')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_request_status_changes');
    }
}
