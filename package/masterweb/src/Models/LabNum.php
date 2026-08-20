<?php

namespace Smt\Masterweb\Models;

use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class LabNum extends Model
{
  use Uuid;
  use SoftDeletes;

  public $incrementing = false;
  protected $table = "tb_lab_num";

  protected $dates = ['deleted_at'];
  protected $primaryKey = 'id_lab_num';
  
  protected $fillable = [
    'id_lab_num',
    'sample_id',
    'sample_type_id',
    'lab_id',
    'mount_lab_num',
    'year_lab_num',
    'permohonan_uji_id',
    'lab_number',
    'is_makanan',
  ];

  public function permohonan_uji()
  {
    return $this->belongsTo(PermohonanUji::class, 'permohonan_uji_id', 'id_permohonan_uji')->withDefault();
  }
  
   public function sample()
  {
    return $this->belongsTo(Sample::class, 'sample_id', 'id_samples')->withDefault();
  }

 


  public function lab()
  {
    return $this->belongsTo(Laboratorium::class, 'lab_id', 'id_laboratorium')->withDefault();
  }

  protected static function booted()
  {
    static::updating(function (self $model) {
      if (!$model->isDirty('lab_number')) {
        return;
      }
      \Smt\Masterweb\Helpers\NomorChangeLogger::record([
        'subject_type' => 'lab_num',
        'subject_id' => (string) $model->getKey(),
        'field_name' => 'lab_number',
        'old_value' => $model->getOriginal('lab_number'),
        'new_value' => $model->getAttribute('lab_number'),
        'event' => 'penggantian',
        'source' => 'kesmas',
      ]);
    });
  }
}