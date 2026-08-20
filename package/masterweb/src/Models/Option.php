<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    //
    protected $table = "ms_options";
    protected $fillable = ['title', 'slogan', 'description', 'keyword', 'footer', 'logo', 'favicon'];
}
