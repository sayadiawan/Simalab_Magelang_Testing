<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationActivity extends Model
{
  protected $table = "ms_verification_activities";
  protected $primaryKey = 'id';
  public $timestamps = false;
  
  protected $fillable = [
    'name',
    'mikro',
    'kimia',
    'klinik',
    'register'
  ];

}
