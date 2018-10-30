<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrontabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crontabs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('todo', 255);
            $table->string('user_id', 20);
            $table->string('src_group_id', 20);
            $table->dateTime('notice_time');
            $table->bigInteger('notice_timestamp');
            $table->timestamps();
            $table->tinyInteger('status', false)->default(2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crontabs');
    }
}
