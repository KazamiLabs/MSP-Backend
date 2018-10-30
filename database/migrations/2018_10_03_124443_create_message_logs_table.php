<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id', 20);
            $table->string('anonymous', 255)->nullable();
            $table->string('src_group_id', 255)->nullable();
            $table->text('raw_message');
            $table->text('message');
            $table->string('font', 20);
            $table->integer('message_id');
            $table->string('message_type', 20);
            $table->string('post_type', 20);
            $table->integer('self_id');
            $table->string('sub_type', 20);
            $table->bigInteger('received_time', false, true);
            $table->dateTime('received_datetime');
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
        Schema::dropIfExists('message_logs');
    }
}
