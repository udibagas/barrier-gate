<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KarcisHilang extends Model
{
    protected $fillable = ['user_id', 'nama', 'alamat', 'no_hp', 'no_plat', 'status'];
}
