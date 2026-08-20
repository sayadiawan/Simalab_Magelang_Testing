<?php

namespace Smt\Masterweb\Models;

use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class StartNum extends Model
{
  protected $table = "ms_start_number";
  use Uuid;
  use SoftDeletes;


  protected $dates = ['deleted_at'];
  protected $primaryKey = 'id_start_number';

  

}