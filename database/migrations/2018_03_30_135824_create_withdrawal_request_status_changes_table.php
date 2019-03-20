<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalRequestStatusChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawal_request_status_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('withdrawal_request_id');
            $table->unsignedInteger('staff_id');
            $table->enum('status', config('selectOptions.withdrawal_requests.status'));
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('withdrawal_request_id')->references('id')->on('withdrawal_requests')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('withdrawal_request_status_changes');
    }
}
