<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'nama_lokasi',
        'alamat_lokasi',
        'info_tambahan_tiket',
        'staff_buka_otomatis',
        'pengunjung_buka_otomatis'
    ];
}
