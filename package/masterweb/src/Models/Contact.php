<?php

namespace Smt\Masterweb\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Contact extends Model
{
    use SoftDeletes;
    protected $table = "tb_contact";
    protected $fillable = ['nama', 'alamat', 'email', 'phone', 'other'];
    public $incrementing = false;
    protected $dates = ['deleted_at'];

    // public function menu()
    // {
    //     return $this->hasOne('App\Menu', 'id', 'menu_id');
    // }
}
