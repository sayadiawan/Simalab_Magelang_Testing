<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TmsResult extends Model
{
    protected $table = 'biolis_results';

    protected $fillable = [
        'result_date',
        'sample_id',
        'parameter_id',
        'parameter_name',
        'patient_name',
        'result_value',
    ];
}
