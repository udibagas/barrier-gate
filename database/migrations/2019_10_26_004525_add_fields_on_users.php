<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsOnUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable();
            $table->string('nomor_kartu')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->string('alamat')->nullable();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->tinyInteger('role')->default(0);
            $table->boolean('status')->default(0);
            $table->string('foto')->nullable();
            $table->date('masa_aktif_kartu')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nip', 'nomor_kartu', 'department_id',
                'alamat', 'jenis_kelamin', 'tempat_lahir',
                'tanggal_lahir', 'role', 'status', 'foto',
                'masa_aktif_kartu'
            ]);
        });
    }
}
