<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BarrierGate extends Model
{

    protected $fillable = [
        'nama', 'jenis', 'controller_ip_address',
        'controller_port', 'camera_snapshot_url',
        'camera_username', 'camera_password', 'camera_status',
        'printer_type', 'printer_device', 'printer_ip_address',
        'printer_status'
    ];
}
