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
            $table->text('to_ping')->nullable(true);
            $table->text('pinged')->nullable(true);
            // 此类字段是节省时间的额外运算开销用
            $table->dateTime('post_date')->default(DB::raw('NOW()'));
            $table->dateTime('post_date_gmt')->default(DB::raw('NOW()'));
            $table->dateTime('post_modified')->default(DB::raw('NOW()'));
            $table->dateTime('post_modified_gmt')->default(DB::raw('NOW()'));
            // 帖子更新原因字段
            $table->longText('post_content_filtered')->nullable(true);
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
