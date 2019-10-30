<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = [
        'nomor_barcode', 'nomor_kartu', 'plat_nomor',
        'is_staff', 'user_id', 'time_in', 'time_out',
        'snapshot_in', 'snapshot_out', 'operator',
        'keterangan'
    ];
}
