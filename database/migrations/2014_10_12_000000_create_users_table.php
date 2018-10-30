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
            $table->increments('id');
            $table->string('user_login', 60);
            $table->string('user_pass');
            $table->string('user_nicename', 50);
            $table->string('user_email', 100)->unique();
            $table->string('user_url', 100);
            $table->dateTime('user_registered');
            $table->string('user_activation_key', 255);
            $table->integer('user_status');
            $table->string('display_name', 250);
            // $table->rememberToken();
            $table->timestamps();
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
