<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnQueueOnAccessLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->boolean('on_queue')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropColumn(['on_queue']);
        });
    }
}
