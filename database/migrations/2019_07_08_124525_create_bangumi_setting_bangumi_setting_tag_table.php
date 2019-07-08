<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBangumiSettingBangumiSettingTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bangumi_setting_bangumi_setting_tag', function (Blueprint $table) {
            $table->bigInteger('tag_id');
            $table->bigInteger('setting_id');
            $table->unique(['tag_id', 'setting_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bangumi_setting_bangumi_setting_tag');
    }
}
