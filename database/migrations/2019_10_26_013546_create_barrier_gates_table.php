<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarrierGatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barrier_gates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama');
            $table->string('jenis', 2);
            $table->string('controller_ip_address', 15);
            $table->smallInteger('controller_port');
            $table->string('camera_snapshot_url')->nullable();
            $table->string('camera_username')->nullable();
            $table->string('camera_password')->nullable();
            $table->boolean('camera_status')->default(0);
            $table->string('printer_type')->nullable();
            $table->string('printer_device')->nullable();
            $table->string('printer_ip_address')->nullable();
            $table->boolean('printer_status')->default(0);
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
        Schema::dropIfExists('barrier_gates');
    }
}
