<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 45)->unique();
            $table->string('email', 191)->unique()->nullable();
            $table->string('password', 60);
            $table->string('nickname', 45)->nullable();
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('middle_name', 45)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('phone', 191)->nullable();
            $table->string('mobile', 191)->nullable();
            $table->string('wechat', 45)->nullable();
            $table->enum('gender', config('selectOptions.common.gender'));
            $table->timestamp('birthday')->nullable();
            $table->enum('birth_date_verification', config('selectOptions.users.verification'))
                ->default(config('selectOptions.users.verification')[0]);
            $table->integer('currency_id')->unsigned()->nullable();
            $table->integer('region_id')->unsigned()->nullable();
            $table->string('birth_city', 191)->nullable();
            $table->string('time_zone', 60)->nullable();
            $table->string('language', 5)->nullable();
            $table->string('document_number', 191)->nullable();
            $table->string('document_issue_code', 191)->nullable();
            $table->timestamp('document_issue_date')->nullable();
            $table->enum('document_verification', config('selectOptions.users.verification'))
                ->default(config('selectOptions.users.verification')[0]);
            $table->enum('blocked', config('selectOptions.users.blocked'));
            $table->timestamp('blocked_chat_until')->nullable();
            $table->enum('loyalty_user', config('selectOptions.users.loyalty_user'));
            $table->string('loyalty_level', 191)->nullable();
            $table->boolean('is_test')->default(false);
            $table->rememberToken()->nullable();
            $table->boolean('activated')->default(true);
            $table->string('activation_token', 191)->nullable();
            $table->timestamp('activation_request_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('bonus_activation_date')->nullable();
            $table->enum('status', config('selectOptions.common.status'));
            $table->string('domain', 92)->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('regions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
