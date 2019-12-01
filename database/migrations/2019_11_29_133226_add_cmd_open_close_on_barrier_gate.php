<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCmdOpenCloseOnBarrierGate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('barrier_gates', function (Blueprint $table) {
            $table->string('cmd_open')->nullable();
            $table->string('cmd_close')->nullable();
            $table->string('serial_device')->nullable();
            $table->smallInteger('serial_baudrate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('barrier_gates', function (Blueprint $table) {
            $table->dropColumn(['cmd_open', 'cmd_close', 'serial_device', 'serial_baudrate']);
        });
    }
}
