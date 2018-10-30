<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_author');
            $table->longText('post_content');
            $table->text('post_title');
            // 文章摘要
            $table->text('post_excerpt');
            $table->string('post_status', 20)->default('publish');
            $table->string('comment_status', 20)->default('open');
            $table->string('ping_status', 20)->default('open');
            $table->string('post_password', 255)->default('');
            $table->string('post_name', 200)->default('');
            $table->text('to_ping');
            $table->text('pinged');
            // 此类字段是节省时间的额外运算开销用
            $table->dateTime('post_date');
            $table->dateTime('post_date_gmt');
            $table->dateTime('post_modified');
            $table->dateTime('post_modified_gmt');
            // 帖子更新原因字段
            $table->longText('post_content_filtered');
            $table->bigInteger('post_parent');
            $table->string('guid', 255);
            $table->integer('menu_order');
            $table->string('post_type', 20);
            $table->string('post_mime_type', 100);
            $table->bigInteger('comment_count');
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
        Schema::dropIfExists('posts');
    }
}
