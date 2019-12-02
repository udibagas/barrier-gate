<?php

use App\AccessLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIsStaffToBoolean extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        AccessLog::where('is_staff', null)->update(['is_staff' => 0]);
        Schema::table('access_logs', function (Blueprint $table) {
            $table->boolean('is_staff')->default(0)->change();
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
            //
        });
    }
}
