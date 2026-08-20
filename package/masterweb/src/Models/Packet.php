<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class Packet extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "ms_packet";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_packet';

    protected $fillable = [
        'id_packet',
        'sample_type_id',
        'name_packet',
        'jenis_makanan_id',
        'price_packet',
        'price_bahan_packet',
        'price_sarana_packet',
        'price_jasa_packet',
        'price_total_packet',
    ];
     
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public function packet_detail()
    {
        return $this->hasMany(PacketDetail::class, 'packet_id', 'id_packet');
    }
    
    public function sampletype()
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id', 'id_sample_type');
    }
}