<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyNullableColumnsToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            //
            $table->string('post_password')->nullable()->change();
            $table->string('to_ping')->nullable()->change();
            $table->string('pinged')->nullable()->change();
            $table->string('post_content_filtered')->nullable()->change();
            $table->string('guid')->nullable()->change();
            $table->string('post_mime_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            //
            $table->string('post_password')->nullable(false)->change();
            $table->string('to_ping')->nullable(false)->change();
            $table->string('pinged')->nullable(false)->change();
            $table->string('post_content_filtered')->nullable(false)->change();
            $table->string('guid')->nullable(false)->change();
            $table->string('post_mime_type')->nullable(false)->change();
        });
    }
}
