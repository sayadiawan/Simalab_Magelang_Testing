<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Privileges extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "ms_privilege";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id';

  function user()
  {
    return $this->hasMany("Smt\Masterweb\Models\User", 'level', 'id');
  }
}