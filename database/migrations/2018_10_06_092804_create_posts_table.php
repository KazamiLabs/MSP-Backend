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
            $table->string('cover')->nullable()->comment('封面');
            $table->text('post_excerpt')->comment('文章摘要');
            $table->string('post_status', 20)->default('draft');
            $table->string('comment_status', 20)->default('open');
            $table->string('ping_status', 20)->default('open');
            $table->string('post_password', 255)->default('');
            $table->string('post_name', 200)->default('');
            $table->text('to_ping')->nullable(true);
            $table->text('pinged')->nullable(true);
            /* 此类字段是节省时间的额外运算开销用 START */
            $table->dateTime('post_date');
            $table->dateTime('post_date_gmt');
            $table->dateTime('post_modified');
            $table->dateTime('post_modified_gmt');
            /* 此类字段是节省时间的额外运算开销用 STOP */
            $table->longText('post_content_filtered')->nullable(true)->comment('帖子更新原因');
            $table->bigInteger('post_parent')->default(0);
            $table->string('guid', 255)->default('');
            $table->integer('menu_order')->default(100);
            $table->string('post_type', 20)->default('post');
            $table->string('post_mime_type', 100)->default('');
            $table->bigInteger('comment_count')->default(0);
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
        Schema::dropIfExists('posts');
    }
}
