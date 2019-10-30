<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nomor_barcode')->nullable();
            $table->string('nomor_kartu')->nullable();
            $table->string('plat_nomor')->nullable();
            $table->string('is_staff')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->string('snapshot_in')->nullable();
            $table->string('snapshot_out')->nullable();
            $table->string('operator')->nullable();
            $table->string('keterangan')->nullable();
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
        Schema::dropIfExists('access_logs');
    }
}
