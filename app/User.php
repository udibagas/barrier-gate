<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use DateTime;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'nip',
        'nomor_kartu', 'department_id',
        'alamat', 'jenis_kelamin', 'tempat_lahir',
        'tanggal_lahir', 'role', 'status', 'foto',
        'masa_aktif_kartu', 'nomor_hp', 'plat_nomor'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'expired' => 'boolean'
    ];

    protected $appends = ['expired', 'expired_in'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getExpiredInAttribute()
    {
        return (int) (new DateTime($this->masa_aktif_kartu))->diff(new DateTime(date('Y-m-d')))->format('%a');
    }

    public function getExpiredAttribute()
    {
        return strtotime(date('Y-m-d')) > strtotime($this->masa_aktif_kartu);
    }
}
