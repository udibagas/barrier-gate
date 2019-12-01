<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BukaManual extends Model
{
    protected $fillable = ['user_id', 'barrier_gate_id', 'alasan'];
}
