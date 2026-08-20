<?php

namespace Smt\Masterweb\Models;

use Illuminate\Notifications\Notifiable;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
  use Notifiable;
  use SoftDeletes;

  protected $table = 'ms_users';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */

  public $incrementing = false;

  protected $fillable = [
    'name', 'email', 'username', 'password', 'photo'
  ];


  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password', 'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  //soft delete
  protected $dates = ['deleted_at'];

  public function roles()
  {
    return $this->belongsToMany('Smt\Masterweb\Models\Role');
  }

  public function privilege()
  {
    return $this->hasOne("Smt\Masterweb\Models\Privileges", 'id', 'level');
  }

  function getlevel()
  {
    return $this->hasOne('Smt\Masterweb\Models\Privileges', 'id', 'level');
  }

  public function pengirimpermohonanujiklinik()
  {
    return $this->hasMany(PermohonanUjiKlinik::class, 'id', 'namapengirim_permohonan_uji_klinik');
  }

  public function analispermohonanujiklinik()
  {
    return $this->hasMany(PermohonanUjiKlinik::class, 'id', 'analis_permohonan_uji_klinik');
  }

  public function devices()
  {
    return $this->hasMany('Smt\Masterweb\Models\UserDevice', 'user_id', 'id');
  }

  public function getJWTIdentifier()
  {
    return $this->getKey();
  }

  public function laboratorium()
  {
    return $this->hasOne('Smt\Masterweb\Models\Laboratorium', 'id_laboratorium', 'laboratory_users');
  }

  public function petugas()
  {
    return $this->belongsTo('Smt\Masterweb\Models\Petugas', 'id_petugas', 'id_petugas');
  }

  /**
   * Return a key value array, containing any custom claims to be added to the JWT.
   *
   * @return array
   */
  public function getJWTCustomClaims()
  {
    return [];
  }
}