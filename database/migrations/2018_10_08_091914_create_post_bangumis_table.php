<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostBangumisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_bangumis', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->string('filename', 255)->comment('种子名称');
            $table->string('filepath', 255)->comment('种子存储路径');
            $table->string('author', 255)->comment('识别的发布组织');
            $table->string('title', 255)->comment('识别的标题');
            $table->string('year', 4)->comment('番剧年份');
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
        Schema::dropIfExists('post_bangumis');
    }
}
