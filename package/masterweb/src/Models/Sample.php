<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Traits\Uuid;
use Smt\Masterweb\Models\NomerLabKesmas;

class Sample extends Model
{
  use SoftDeletes;
  use Uuid;

  protected $table = "tb_samples";
  protected $dates = ['deleted_at'];
  public $incrementing = false;
  protected $primaryKey = 'id_samples';

  protected $fillable = [
    'id_samples',
    'permohonan_uji_id',
    'group_id',
    'typesample_samples',
    'codesample_samples',
    'codesample_samples_manual',
    'name_pelanggan',
    'datesampling_samples',
    'date_sending',
    'titik_pengambilan',
    'cost_samples',
    'note_samples',
    'pengambil_sampel',
    'count_id',
    'program_samples',
    'packet_id',
    'name_send_sample',
    'code_sample_customer',
    'is_sampling',
    'cost_sampling_samples',
    'is_nomor_sampel_manual',
    'is_nomor_laboratorium_manual',
  ];

  protected $casts = [
    'is_nomor_sampel_manual' => 'boolean',
    'is_nomor_laboratorium_manual' => 'boolean',
  ];

  /** @var bool|null Cache Schema::hasColumn per proses (hindari error jika migrasi belum dijalankan) */
  protected static $schemaHasNomorSampelManualColumn;

  /** @var bool|null */
  protected static $schemaHasNomorLaboratoriumManualColumn;

  protected static function booted()
  {
    static::saving(function (Sample $model) {
      if (self::$schemaHasNomorSampelManualColumn === null) {
        self::$schemaHasNomorSampelManualColumn = Schema::hasColumn($model->getTable(), 'is_nomor_sampel_manual');
      }
      if (self::$schemaHasNomorLaboratoriumManualColumn === null) {
        self::$schemaHasNomorLaboratoriumManualColumn = Schema::hasColumn($model->getTable(), 'is_nomor_laboratorium_manual');
      }
      if (!self::$schemaHasNomorSampelManualColumn) {
        $model->offsetUnset('is_nomor_sampel_manual');
      }
      if (!self::$schemaHasNomorLaboratoriumManualColumn) {
        $model->offsetUnset('is_nomor_laboratorium_manual');
      }
    });

    static::updating(function (Sample $model) {
      foreach (['codesample_samples', 'count_id'] as $field) {
        if (!$model->isDirty($field)) {
          continue;
        }
        \Smt\Masterweb\Helpers\NomorChangeLogger::record([
          'subject_type' => 'sample',
          'subject_id' => (string) $model->getKey(),
          'field_name' => $field,
          'old_value' => $model->getOriginal($field),
          'new_value' => $model->getAttribute($field),
          'event' => 'penggantian',
          'source' => 'kesmas',
        ]);
      }
    });
  }

  // function user(){
  //     return $this->hasMany("App\User",'level','id');
  // }

  public function sampletype()
  {
    return $this->belongsTo(SampleType::class, 'typesample_samples', 'id_sample_type')->where('deleted_at', NULL);
  }

  public function packet()
  {
    return $this->belongsTo(Packet::class, 'packet_id', 'id_packet');
  }

  /**
   * Tarif laporan/register dari nilai tersimpan + harga paket.
   * Sample paket sering tersimpan cost_samples = jumlah harga parameter (lebih rendah dari harga paket).
   * Pakai harga paket kecuali cost sudah kelipatan harga paket (qty) atau lebih tinggi (custom).
   */
  public static function resolveReportTarifFromValues(float $stored, float $packetPrice): float
  {
    if ($packetPrice <= 0) {
      return $stored;
    }

    if ($stored <= 0) {
      return $packetPrice;
    }

    $ratio = $stored / $packetPrice;
    if (abs($ratio - round($ratio)) < 0.001 && (int) round($ratio) >= 1) {
      return $stored;
    }

    if ($stored < $packetPrice) {
      return $packetPrice;
    }

    return $stored;
  }

  public function resolveReportTarif(): float
  {
    $stored = (float) ($this->cost_samples ?? 0);

    if (empty($this->packet_id)) {
      return $stored;
    }

    $packet = $this->relationLoaded('packet') ? $this->packet : $this->packet()->first();
    if (!$packet) {
      return $stored;
    }

    return self::resolveReportTarifFromValues(
      $stored,
      (float) ($packet->price_total_packet ?? 0)
    );
  }


  public function jenis_makanan()
  {
    return $this->belongsTo(JenisMakanan::class, 'jenis_makanan_id', 'id_jenis_makanan')->where('deleted_at', NULL);
  }

  public function permohonanuji()
  {
    return $this->hasOne(PermohonanUji::class, 'id_permohonan_uji', 'permohonan_uji_id');
  }

  public function namaPengambilDisplay($default = '-'): string
  {
    $nama = $this->name_send_sample ?? optional($this->permohonanuji)->name_sampling;

    return ($nama !== null && trim((string) $nama) !== '') ? trim((string) $nama) : $default;
  }

  public function namaPelangganDisplay($default = '-'): string
  {
    $nama = $this->name_pelanggan ?? optional(optional($this->permohonanuji)->customer)->name_customer;

    return ($nama !== null && trim((string) $nama) !== '') ? trim((string) $nama) : $default;
  }

  public function detailAlamatSamplingDisplay($default = '-'): string
  {
    $alamat = optional($this->permohonanuji)->detail_alamat_sampling;

    return ($alamat !== null && trim((string) $alamat) !== '') ? trim((string) $alamat) : $default;
  }

  public function customerAddressDisplay($default = '-'): string
  {
    $address = optional(optional($this->permohonanuji)->customer)->address_customer;

    return ($address !== null && trim((string) $address) !== '') ? trim((string) $address) : $default;
  }

  public function namaJenisMakananPlain($default = ''): string
  {
    if (!isset($this->nama_jenis_makanan) || trim((string) $this->nama_jenis_makanan) === '') {
      return $default;
    }

    $text = preg_replace('/<br\s*\/?>/i', "\n", (string) $this->nama_jenis_makanan);
    $text = preg_replace('/<\/?p[^>]*>/i', '', $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim($text) !== '' ? trim($text) : $default;
  }

  public function titikSampelDisplay($default = '-'): string
  {
    if (!isset($this->titik_pengambilan) || trim((string) $this->titik_pengambilan) === '') {
      return $default;
    }

    $text = preg_replace('/<\/?p[^>]*>/i', '', (string) $this->titik_pengambilan);
    $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim($text);

    return $text !== '' ? $text : $default;
  }

  /**
   * Jenis sampel kategori makanan (mis. Minuman) untuk LHU mikro makanan.
   *
   * @param  array<string, string>|null  $jenisMakananNames  Map id_jenis_makanan => name (hindari N+1 di cetak)
   */
  public function jenisSampelMakananDisplay($default = '-', ?array $jenisMakananNames = null): string
  {
    $id = $this->jenis_makanan_id ?? null;
    if ($id !== null && $id !== '') {
      if (is_array($jenisMakananNames) && isset($jenisMakananNames[$id])) {
        $name = trim((string) $jenisMakananNames[$id]);
        if ($name !== '') {
          return $name;
        }
      }

      if ($this->relationLoaded('jenis_makanan') && $this->jenis_makanan) {
        $name = trim((string) ($this->jenis_makanan->name_jenis_makanan ?? ''));
        if ($name !== '') {
          return $name;
        }
      }
    }

    $nama = $this->namaJenisMakananPlain('');
    if ($nama !== '') {
      return $nama;
    }

    return $default;
  }

  public function jenisSampelDisplay(string $separator = ' - ', $default = '-'): string
  {
    $type = trim((string) ($this->name_sample_type ?? ''));
    $nama = $this->namaJenisMakananPlain('');

    if ($nama === '') {
      return $type !== '' ? $type : $default;
    }

    return $type !== '' ? $type . $separator . $nama : $nama;
  }

  public function syncNamaPengambil(?string $nama): void
  {
    if ($nama === null || trim($nama) === '') {
      return;
    }

    $this->name_send_sample = $nama;

    if (!$this->permohonan_uji_id) {
      return;
    }

    if (!$this->relationLoaded('permohonanuji')) {
      $this->load('permohonanuji');
    }

    if ($this->permohonanuji) {
      $this->permohonanuji->name_sampling = $nama;
      $this->permohonanuji->save();
    }
  }

  public function samplemethod()
  {
    return $this->hasMany(SampleMethod::class, 'sample_id', 'id_samples');
  }

  public function labnum()
  {
    return $this->hasMany(LabNum::class, 'sample_id', 'id_samples');
  }

  public function labnumByLab($id_lab)
  {
    return $this->hasMany(LabNum::class, 'sample_id', 'id_samples')->where('lab_id', $id_lab);
  }


  public function pengesahanhasil()
  {
    return $this->belongsTo(PengesahanHasil::class, 'id_samples', 'sample_id');
  }

  public function sampleresult()
  {
    return $this->hasMany(SampleResult::class, 'sample_id', 'id_samples');
  }

  /**
   * Get sibling samples with the same group_id
   * These are samples created in the same input session
   */
  public function siblingSamples()
  {
    return $this->hasMany(Sample::class, 'group_id', 'group_id')
                ->where('id_samples', '!=', $this->id_samples);
  }

  /**
   * Get all samples in the same group (including self)
   */
  public function groupSamples()
  {
    return Sample::where('group_id', $this->group_id)->get();
  }

  /**
   * Pulihkan codesample_samples dari cadangan manual (jika berbeda).
   */
  public static function restoreKesmasManualSampleCodes(): int
  {
    if (!Schema::hasColumn((new static)->getTable(), 'codesample_samples_manual')) {
      return 0;
    }

    $restored = 0;
    static::query()
      ->where('is_nomor_sampel_manual', true)
      ->whereNotNull('codesample_samples_manual')
      ->where('codesample_samples_manual', '!=', '')
      ->chunkById(200, function ($samples) use (&$restored) {
        foreach ($samples as $sample) {
          $manual = trim((string) $sample->codesample_samples_manual);
          if ($manual === '' || $manual === (string) $sample->codesample_samples) {
            continue;
          }

          $parts = explode('/', $manual);
          $seq = isset($parts[1]) ? (int) preg_replace('/\D/', '', (string) $parts[1]) : 0;

          $sample->codesample_samples = $manual;
          if ($seq > 0) {
            $sample->count_id = $seq;
          }
          $sample->save();
          $restored++;
        }
      }, 'id_samples');

    return $restored;
  }

  /**
   * Segmen kode sampel untuk cetak hasil (otomatis: pecah berdasarkan '/').
   * Jika nomor sampel manual, tampilkan kode lengkap agar format bebas tidak putus.
   */
  public static function codesampleSegmentForPrint(?string $codesample, bool $isNomorSampelManual, int $partIndex): string
  {
    $full = trim((string) $codesample);
    if ($full === '') {
      return '';
    }
    if ($isNomorSampelManual) {
      return $full;
    }
    $parts = explode('/', $full);

    return isset($parts[$partIndex]) ? trim((string) $parts[$partIndex]) : $full;
  }

  public function codesampleSegmentForPrintInstance(int $partIndex): string
  {
    return self::codesampleSegmentForPrint(
      $this->codesample_samples,
      (bool) $this->is_nomor_sampel_manual,
      $partIndex
    );
  }

  /**
   * Nomor urut sampel untuk cetak (rentang min–max di nota/permintaan).
   * Format: {jenis}.{lab}/{urut}/{tahun} atau {jenis}.{lab}/{lab2}/{urut}/{tahun}
   */
  public static function codesampleNomorUrutForPrint(?string $codesample, bool $isNomorSampelManual): string
  {
    $full = trim((string) $codesample);
    if ($full === '') {
      return '';
    }
    if ($isNomorSampelManual) {
      return $full;
    }
    $parts = explode('/', $full);
    if (count($parts) >= 4) {
      return trim((string) ($parts[2] ?? $full));
    }
    if (count($parts) >= 3) {
      return trim((string) ($parts[1] ?? $full));
    }

    return $full;
  }

  /**
   * Sel tabel / daftar: kode lengkap + badge jika nomor sampel manual (Kesmas).
   *
   * @param  \Illuminate\Database\Eloquent\Model|object|array|null  $row  harus punya codesample_samples; is_nomor_sampel_manual opsional
   */
  public static function codesampleTableCellHtmlFrom($row, bool $withManualBadge = true): string
  {
    if ($row === null) {
      return e('-');
    }
    $c = trim((string) data_get($row, 'codesample_samples', ''));
    if ($c === '') {
      return e('-');
    }
    $out = e($c);
    if ($withManualBadge && data_get($row, 'is_nomor_sampel_manual')) {
      $out .= ' <span class="badge badge-secondary badge-pill">Manual</span>';
    }

    return $out;
  }

  /**
   * Build formatted lab number for this sample only.
   *
   * Format example:
   * - 449.5/01/0001/2026 (Kimia)
   * - 449.5/02/0002/2026 (Mikrobiologi)
   *
   * Priority:
   *  1. tb_nomer_lab_kesmas  — assigned during pengesahan (authoritative)
   *  2. tb_lab_num           — legacy per-sample number from creation
   *  3. tb_sample_method     — lab code fallback when no number yet
   */
  public function getNomorLab($prefix = '449.5', $year = null, $labId = null)
  {
    $year    = $year ?: date('Y');
    $labCode = null;
    $number  = null;

    // 1. Prioritize tb_nomer_lab_kesmas (assigned during pengesahan hasil)
    if ($this->permohonan_uji_id && $this->typesample_samples) {
      $kesmasQuery = NomerLabKesmas::where('permohonan_uji_id', $this->permohonan_uji_id)
        ->where('sample_type_id', $this->typesample_samples);
      if ($labId) {
        $kesmasQuery->where('laboratorium_id', $labId);
      }
      $kesmasRecord = $kesmasQuery->first();

      if ($kesmasRecord && $kesmasRecord->nomer_lab) {
        // Determine lab code from tb_nomer_lab_kesmas laboratorium
        $targetLabId = $labId ?? $kesmasRecord->laboratorium_id;
        $lab = \Smt\Masterweb\Models\Laboratorium::find($targetLabId);
        if ($lab) {
          $kode    = strtoupper((string) ($lab->kode_laboratorium ?? ''));
          $labCode = $kode === 'KIM' ? '01' : ($kode === 'MBI' ? '02' : null);
        }
        if ($labCode) {
          $number = str_pad((string) $kesmasRecord->nomer_lab, 4, '0', STR_PAD_LEFT);
          return $prefix . '/' . $labCode . '/' . $number . '/' . ($kesmasRecord->year ?? $year);
        }
      }
    }

    // 2. Determine lab code from tb_lab_num or tb_sample_method (for placeholder display).
    $labNumQuery = $this->labnum()->with('lab');
    if ($labId) {
      $labNumQuery = $labNumQuery->where('lab_id', $labId);
    }
    $labNum = $labNumQuery->first();
    if ($labNum && $labNum->lab) {
      $kodeLab = strtoupper((string) ($labNum->lab->kode_laboratorium ?? ''));
      $labCode = $kodeLab === 'KIM' ? '01' : ($kodeLab === 'MBI' ? '02' : null);
    }

    if ($labCode === null) {
      $sampleMethodQuery = $this->samplemethod()->with('laboratorium');
      if ($labId) {
        $sampleMethodQuery = $sampleMethodQuery->where('laboratorium_id', $labId);
      }
      $sampleMethod = $sampleMethodQuery->first();
      if ($sampleMethod && $sampleMethod->laboratorium) {
        $kodeLab = strtoupper((string) ($sampleMethod->laboratorium->kode_laboratorium ?? ''));
        $labCode = $kodeLab === 'KIM' ? '01' : ($kodeLab === 'MBI' ? '02' : null);
      }
    }

    if ($labCode === null) {
      return null;
    }

    // Nomor lab belum ditetapkan — tampilkan placeholder titik-titik
    return $prefix . '/' . $labCode . '/............/' . $year;
  }

  /**
   * Nomor lab yang sudah ditetapkan; null jika belum ada (bukan placeholder titik-titik).
   */
  public function getAssignedNomorLabOrNull($prefix = '449.5', $year = null, $labId = null): ?string
  {
    $nomor = $this->getNomorLab($prefix, $year, $labId);
    if ($nomor === null || preg_match('/\.{2,}/', $nomor)) {
      return null;
    }

    return $nomor;
  }

  /**
   * Compatibility helper: returns value only when this sample is Kimia.
   */
  public function getNomorLabKimia($prefix = '449.5', $year = null)
  {
    $nomorLab = $this->getNomorLab($prefix, $year);
    if (!$nomorLab || strpos($nomorLab, '/01/') === false) {
      return null;
    }

    return $nomorLab;
  }

  /**
   * Compatibility helper: returns value only when this sample is Mikrobiologi.
   */
  public function getNomorLabMikro($prefix = '449.5', $year = null)
  {
    $nomorLab = $this->getNomorLab($prefix, $year);
    if (!$nomorLab || strpos($nomorLab, '/02/') === false) {
      return null;
    }

    return $nomorLab;
  }
}