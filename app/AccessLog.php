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

    protected $appends = ['durasi'];

    public function getDurasiAttribute()
    {
        $in = new \DateTime($this->time_in);
        $out = new \DateTime($this->time_out);
        $interval = $in->diff($out);
        return $interval->format('%H:%I:%S');
    }
}
