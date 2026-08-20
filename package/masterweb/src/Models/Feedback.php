<?php

namespace Smt\Masterweb\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Feedback extends Model
{

    use SoftDeletes;
    use Uuid;

    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $table = "tb_feedback";
    protected $fillable = ['name', 'email', 'phone', 'message'];

    // public function menu()
    // {
    //     return $this->hasOne('App\Menu', 'id', 'menu_id');
    // }
}
