<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJenisKartuIdentitasNomorIdentitasDiKarcisHilang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('karcis_hilangs', function (Blueprint $table) {
            $table->string('jenis_kartu_identitas')->nullable();
            $table->string('nomor_kartu_identitas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('karcis_hilangs', function (Blueprint $table) {
            $table->dropColumn(['jenis_kartu_identitas', 'nomor_kartu_identitas']);
        });
    }
}
