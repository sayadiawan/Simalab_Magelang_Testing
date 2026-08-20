<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class PacketDetail extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_packet_detail";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_packet_detail';

     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
   
     public function packet()
     {
       return $this->belongsTo(PacketDetail::class, 'packet_id', 'id_packet');
     }

     public function method()
     {
       return $this->belongsTo(Method::class, 'method_id', 'id_method');
     }
}