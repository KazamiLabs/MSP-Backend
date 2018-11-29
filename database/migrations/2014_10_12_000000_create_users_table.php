<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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
        $defaultTz = Config::get('app.timezone');
        Schema::create('users', function (Blueprint $table) use ($defaultTz) {
            $table->increments('id');
            $table->string('name');
            $table->string('email', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('nicename', 50)->default('');
            $table->string('person_index', 100)->default('');
            $table->string('timezone')->default($defaultTz);
            $table->dateTime('registered')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('activation_key', 255)->default('');
            $table->integer('status')->default(1);
            $table->rememberToken();
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
