<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('image');
            $table->string('qr_code');
            $table->double('amount', 8, 2);
            $table->string('phone_number')->unique();
            $table->string('email')->unique();
            $table->string('phone_otp', 10);
            $table->string('email_otp', 10);
            $table->tinyInteger('is_phone_number_verified')->default(0);
            $table->tinyInteger('is_email_verified')->default(0);
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();
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
