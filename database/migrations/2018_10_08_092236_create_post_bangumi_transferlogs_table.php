<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostBangumiTransferlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_bangumi_transferlogs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->string('site', 255);
            $table->string('sitedriver', 255);
            $table->string('site_id', 255);
            $table->string('sync_state', 255)->default('success');
            $table->string('log_file', 255);
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
        Schema::dropIfExists('post_bangumi_transferlogs');
    }
}
