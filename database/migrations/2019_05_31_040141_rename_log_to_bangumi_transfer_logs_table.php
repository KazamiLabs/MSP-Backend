<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameLogToBangumiTransferLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bangumi_transfer_logs', function (Blueprint $table) {
            //
            $table->renameColumn('log_file', 'log');
        });

        Schema::table('bangumi_transfer_logs', function (Blueprint $table) {
            //
            $table->text('log')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bangumi_transfer_logs', function (Blueprint $table) {
            //
            $table->renameColumn('log', 'log_file');
        });

        Schema::table('bangumi_transfer_logs', function (Blueprint $table) {
            //
            $table->string('log_file', 255)->change();
        });
    }
}
