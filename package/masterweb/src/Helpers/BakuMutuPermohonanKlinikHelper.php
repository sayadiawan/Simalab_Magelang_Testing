<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;

class BakuMutuPermohonanKlinikHelper
{
  public static function hasSnapshotColumns(): bool
  {
    return Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'min_baku_mutu_permohonan_uji_parameter_klinik')
      && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'max_baku_mutu_permohonan_uji_parameter_klinik')
      && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'equal_baku_mutu_permohonan_uji_parameter_klinik')
      && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'kesimpulan_baku_mutu_permohonan_uji_parameter_klinik')
      && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik');
  }

  public static function normalizePasienGender(?string $gender): ?string
  {
    if ($gender === null || $gender === '') {
      return null;
    }

    return in_array(strtoupper($gender), ['L', 'M', 'MALE'], true) ? 'L' : 'P';
  }

  /**
   * Varian ejaan parameter master (paket haji vs klinik umum).
   */
  public static function canonicalParameterSatuanName(?string $name): string
  {
    $normalized = trim(preg_replace('/\s+/u', ' ', mb_strtolower((string) $name, 'UTF-8')));
    if ($normalized === '') {
      return '';
    }

    $normalized = trim(preg_replace('/\s*\(.*?\)\s*/u', ' ', $normalized));
    $normalized = trim(preg_replace('/\s+/u', ' ', $normalized));

    static $aliases = [
      'creatinine' => ['creatinine', 'kreatinin'],
      'gula darah 2jpp' => ['gula darah 2jpp', 'gula darah 2 jam pp', 'gula darah 2jam pp', '2 jam pp', 'gd 2jpp', 'gd 2 jam pp'],
      'nitrit' => ['nitrit', 'nitrat'],
      'kejernihan' => ['kejernihan', 'kekeruhan'],
      'triglyceride' => ['triglyceride', 'trigliserid', 'trigliseride'],
      'cholesterol total' => ['cholesterol total', 'cholesterol', 'kolesterol total', 'kolesterol'],
    ];

    foreach ($aliases as $canonical => $list) {
      if (in_array($normalized, $list, true)) {
        return $canonical;
      }
    }

    return $normalized;
  }

  /**
   * ID parameter_satuan lain dengan nama kanonik sama (untuk fallback baku mutu haji).
   *
   * @return string[]
   */
  public static function resolveAliasSatuanIds(string $satuanId): array
  {
    $source = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $satuanId)
      ->whereNull('deleted_at')
      ->first(['id_parameter_satuan_klinik', 'name_parameter_satuan_klinik']);

    if (!$source) {
      return [];
    }

    $canonical = self::canonicalParameterSatuanName($source->name_parameter_satuan_klinik ?? '');
    if ($canonical === '') {
      return [];
    }

    $candidates = ParameterSatuanKlinik::whereNull('deleted_at')
      ->get(['id_parameter_satuan_klinik', 'name_parameter_satuan_klinik']);

    $ids = [];
    foreach ($candidates as $row) {
      $id = (string) $row->id_parameter_satuan_klinik;
      if ($id === $satuanId) {
        continue;
      }
      if (self::canonicalParameterSatuanName($row->name_parameter_satuan_klinik ?? '') === $canonical) {
        $ids[] = $id;
      }
    }

    return $ids;
  }

  public static function isBakuMutuHaji(?string $bakuMutuId): bool
  {
    if ($bakuMutuId === null || $bakuMutuId === '') {
      return false;
    }

    return BakuMutu::where('id_baku_mutu', $bakuMutuId)
      ->where('is_haji', 1)
      ->whereNull('deleted_at')
      ->exists();
  }

  public static function loadBakuMutuForParameter(string $jenisId, string $satuanId, int $isHaji = 0): Collection
  {
    $query = BakuMutu::query()
      ->where('parameter_jenis_klinik_id', $jenisId)
      ->where('parameter_satuan_klinik_id', $satuanId)
      ->whereNull('deleted_at');

    if ($isHaji === 1) {
      $query->where('is_haji', 1);
    } else {
      $query->where(function ($q) {
        $q->where('is_haji', 0)->orWhereNull('is_haji');
      });
    }

    $rows = $query->get();

    // Paket haji kadang memakai parameter master berbeda ejaan (Kreatinin vs Creatinine).
    if ($isHaji === 1 && $rows->isEmpty()) {
      $aliasIds = self::resolveAliasSatuanIds($satuanId);
      foreach ($aliasIds as $aliasId) {
        $aliasRows = BakuMutu::query()
          ->where('parameter_jenis_klinik_id', $jenisId)
          ->where('parameter_satuan_klinik_id', $aliasId)
          ->where('is_haji', 1)
          ->whereNull('deleted_at')
          ->get();
        if ($aliasRows->isNotEmpty()) {
          return $aliasRows;
        }
      }
    }

    // Beberapa parameter urin haji belum punya master is_haji=1; fallback ke klinik umum
    // (selaras dengan resolveBakuMutuForPermohonanParameter di controller).
    if ($isHaji === 1 && $rows->isEmpty()) {
      $rows = self::loadNonHajiBakuMutuForParameter($jenisId, $satuanId);
    }

    return $rows;
  }

  private static function loadNonHajiBakuMutuForParameter(string $jenisId, string $satuanId): Collection
  {
    $query = function (string $lookupSatuanId) use ($jenisId) {
      return BakuMutu::query()
        ->where('parameter_jenis_klinik_id', $jenisId)
        ->where('parameter_satuan_klinik_id', $lookupSatuanId)
        ->where(function ($q) {
          $q->where('is_haji', 0)->orWhereNull('is_haji');
        })
        ->whereNull('deleted_at')
        ->get();
    };

    $rows = $query($satuanId);
    if ($rows->isNotEmpty()) {
      return $rows;
    }

    foreach (self::resolveAliasSatuanIds($satuanId) as $aliasId) {
      $aliasRows = $query($aliasId);
      if ($aliasRows->isNotEmpty()) {
        return $aliasRows;
      }
    }

    return collect();
  }

  /**
   * Satuan ID yang punya baku mutu haji (asli atau alias) untuk lookup resolve.
   */
  public static function resolveHajiLookupSatuanId(string $jenisId, string $satuanId): string
  {
    $direct = BakuMutu::query()
      ->where('parameter_jenis_klinik_id', $jenisId)
      ->where('parameter_satuan_klinik_id', $satuanId)
      ->where('is_haji', 1)
      ->whereNull('deleted_at')
      ->exists();

    if ($direct) {
      return $satuanId;
    }

    foreach (self::resolveAliasSatuanIds($satuanId) as $aliasId) {
      $exists = BakuMutu::query()
        ->where('parameter_jenis_klinik_id', $jenisId)
        ->where('parameter_satuan_klinik_id', $aliasId)
        ->where('is_haji', 1)
        ->whereNull('deleted_at')
        ->exists();
      if ($exists) {
        return $aliasId;
      }
    }

    return $satuanId;
  }

  /**
   * Mode "Sama semua" di master baku mutu: satu teks nilai untuk semua baris di laporan.
   */
  public static function isMassalNilaiDiLaporan($allBakuMutu): bool
  {
    $collection = $allBakuMutu instanceof Collection ? $allBakuMutu : collect($allBakuMutu);

    return $collection->contains(function ($item) {
      $flag = is_array($item)
        ? ($item['is_massal_nilai_di_laporan'] ?? 0)
        : ($item->is_massal_nilai_di_laporan ?? 0);

      return (int) $flag === 1;
    });
  }

  /**
   * Nilai_baku_mutu bersama untuk mode massal (mis. "L : 0.8 - 1.3  P : 0.6 - 1.2").
   */
  public static function sharedMassalNilaiBakuMutu($allBakuMutu): ?string
  {
    $collection = $allBakuMutu instanceof Collection ? $allBakuMutu : collect($allBakuMutu);

    foreach ($collection as $item) {
      $nilai = is_array($item)
        ? ($item['nilai_baku_mutu'] ?? null)
        : ($item->nilai_baku_mutu ?? null);
      if ($nilai !== null && $nilai !== '') {
        return $nilai;
      }
    }

    return null;
  }

  public static function resolveMassalKeteranganDilaporan($allBakuMutu): ?string
  {
    if (!self::isMassalNilaiDiLaporan($allBakuMutu)) {
      return null;
    }

    return self::sharedMassalNilaiBakuMutu($allBakuMutu);
  }

  /**
   * Kembalikan satu baris baku mutu terbaik untuk pasien (untuk FK/satuan/method).
   */
  public static function resolveMatchedBakuMutu(string $jenisId, string $satuanId, ?string $pasienGender, $pasienUmur, int $isHaji = 0)
  {
    $rows = self::loadBakuMutuForParameter($jenisId, $satuanId, $isHaji);

    return self::resolveForPasien($rows, $pasienGender, $pasienUmur);
  }

  /**
   * Kembalikan Collection semua baris is_normal yang cocok gender/umur pasien.
   * Jika ada ≥2 baris, akan digunakan untuk snapshot kolom (min/max/equal dipisah koma).
   */
  public static function resolveAllNormalMatchedBakuMutu(string $jenisId, string $satuanId, ?string $pasienGender, $pasienUmur, int $isHaji = 0): Collection
  {
    $rows = self::loadBakuMutuForParameter($jenisId, $satuanId, $isHaji);

    return self::resolveAllNormalForPasien($rows, $pasienGender, $pasienUmur);
  }

  /**
   * Satu baris terbaik dari koleksi baku mutu (untuk FK/satuan/method).
   */
  public static function resolveForPasien($allBakuMutu, ?string $pasienGender, $pasienUmur)
  {
    $col = self::resolveAllNormalForPasien($allBakuMutu, $pasienGender, $pasienUmur);

    return $col->first();
  }

  /**
   * Semua baris is_normal yang cocok gender/umur pasien.
   * Jika tidak ada yang is_normal, kembalikan single fallback.
   */
  public static function resolveAllNormalForPasien($allBakuMutu, ?string $pasienGender, $pasienUmur): Collection
  {
    $empty = collect();

    if ($allBakuMutu->isEmpty()) {
      return $empty;
    }

    $generalRows = $allBakuMutu->filter(function ($item) {
      return (int) ($item->is_khusus_baku_mutu ?? 0) === 0;
    });
    $specificRows = $allBakuMutu->filter(function ($item) {
      return (int) ($item->is_khusus_baku_mutu ?? 0) === 1;
    });

    if ($specificRows->isEmpty()) {
      $first = self::matchByGenderFallback($generalRows->isNotEmpty() ? $generalRows : $allBakuMutu, $pasienGender);

      return $first ? collect([$first]) : $empty;
    }

    if ($specificRows->count() === 1) {
      return collect([$specificRows->first()]);
    }

    $normalRows = $specificRows->filter(function ($item) {
      return (int) ($item->is_normal ?? 0) === 1;
    });

    if ($normalRows->isEmpty()) {
      $first = self::matchByGenderFallback($specificRows, $pasienGender) ?: $specificRows->first();

      return $first ? collect([$first]) : $empty;
    }

    // Filter semua is_normal yang cocok gender+umur
    $matched = $normalRows->filter(function ($item) use ($pasienGender, $pasienUmur) {
      $hasGender = isset($item->gender_baku_mutu) && $item->gender_baku_mutu !== null && $item->gender_baku_mutu !== '';
      $hasUmur   = isset($item->minimal_umur_baku_mutu) && isset($item->maksimal_umur_baku_mutu);

      // Tidak ada pembatas gender/umur → selalu cocok
      if (!$hasGender && !$hasUmur) {
        return true;
      }

      $genderMatch    = $hasGender && $item->gender_baku_mutu === $pasienGender;
      $umurMatch      = $hasUmur && $pasienUmur !== null
        && $item->minimal_umur_baku_mutu <= $pasienUmur
        && $item->maksimal_umur_baku_mutu >= $pasienUmur;
      $umurMatchOnly  = $umurMatch && !$hasGender;
      $genderMatchOnly = $genderMatch && !$hasUmur;

      return ($genderMatch && $umurMatch) || $umurMatchOnly || $genderMatchOnly;
    });

    if ($matched->isNotEmpty()) {
      return $matched->values();
    }

    // Fallback: cocok gender saja
    if ($pasienGender !== null && $pasienGender !== '') {
      $byGender = $normalRows->filter(function ($item) use ($pasienGender) {
        return isset($item->gender_baku_mutu) && $item->gender_baku_mutu === $pasienGender;
      });
      if ($byGender->isNotEmpty()) {
        return $byGender->values();
      }
    }

    // Fallback: cocok umur saja
    $byUmur = $normalRows->filter(function ($item) use ($pasienUmur) {
      return (!isset($item->minimal_umur_baku_mutu) && !isset($item->maksimal_umur_baku_mutu))
        || ($pasienUmur !== null
          && isset($item->minimal_umur_baku_mutu)
          && isset($item->maksimal_umur_baku_mutu)
          && $item->minimal_umur_baku_mutu <= $pasienUmur
          && $item->maksimal_umur_baku_mutu >= $pasienUmur);
    });
    if ($byUmur->isNotEmpty()) {
      return $byUmur->values();
    }

    return collect([$normalRows->first()]);
  }

  /**
   * Apakah keterangan_dilaporan perlu diformat ulang dari kolom min/max/equal
   * (mis. snapshot hanya "200" padahal max=200 dan is_normal=1 → "< 200").
   */
  public static function keteranganNeedsMinMaxFormatting(?string $keterangan): bool
  {
    if ($keterangan === null || $keterangan === '' || $keterangan === '-') {
      return true;
    }

    if (stripos($keterangan, '<table') !== false) {
      return false;
    }

    $plain = trim(strip_tags($keterangan));
    if ($plain === '') {
      return true;
    }

    if (preg_match('/[<>≤≥=]|&lt;|&gt;|&le;|&ge;|&#60;|&#8804;|&#8805;/u', $plain)) {
      return false;
    }

    if (preg_match('/\d\s*-\s*\d/', $plain)) {
      return false;
    }

    if (strpos($plain, ',') !== false) {
      return false;
    }

    return true;
  }

  public static function hasStructuredBakuMutuRange(?string $min, ?string $max, ?string $equal): bool
  {
    return ($min !== null && $min !== '')
      || ($max !== null && $max !== '')
      || ($equal !== null && $equal !== '');
  }

  /**
   * Format satu baris nilai rujukan dari min/max/equal/is_normal.
   */
  public static function formatRangeLine(
    ?string $min,
    ?string $max,
    ?string $equal,
    ?string $nilaiBakuMutu = null,
    ?string $kesimpulanBakuMutu = null,
    int $isNormal = 0
  ): ?string {
    $hasMin = $min !== null && $min !== '';
    $hasMax = $max !== null && $max !== '';
    $rangePart = null;

    if ($hasMin && $hasMax) {
      $rangePart = $min . ' - ' . $max;
    } elseif ($hasMin) {
      $rangePart = '≥ ' . $min;
    } elseif ($hasMax) {
      $rangePart = ($isNormal === 1 ? '< ' : '≤ ') . $max;
    } elseif ($equal !== null && $equal !== '') {
      $rangePart = '= ' . $equal;
    } elseif ($nilaiBakuMutu !== null && $nilaiBakuMutu !== '') {
      $rangePart = $nilaiBakuMutu;
    }

    if ($rangePart === null || $rangePart === '' || $rangePart === '-') {
      return null;
    }

    $kesimpulan = trim(strip_tags((string) ($kesimpulanBakuMutu ?? '')));
    if ($kesimpulan !== '') {
      return $kesimpulan . ' : ' . $rangePart;
    }

    return $rangePart;
  }

  /**
   * @param object|array $item Baris baku mutu (min, max, equal, nilai_baku_mutu, kesimpulan_baku_mutu, is_normal).
   */
  public static function formatKeteranganFromBakuMutuItem($item): ?string
  {
    if ($item === null) {
      return null;
    }

    $get = function (string $field) use ($item) {
      if (is_array($item)) {
        return $item[$field] ?? null;
      }

      return $item->$field ?? null;
    };

    return self::formatRangeLine(
      $get('min'),
      $get('max'),
      $get('equal'),
      $get('nilai_baku_mutu'),
      $get('kesimpulan_baku_mutu'),
      (int) ($get('is_normal') ?? 0)
    );
  }

  public static function formatKeteranganIfNeeded(
    ?string $keterangan,
    ?string $min,
    ?string $max,
    ?string $equal,
    ?string $kesimpulanBakuMutu = null,
    int $isNormal = 0
  ): ?string {
    if (!self::hasStructuredBakuMutuRange($min, $max, $equal)) {
      return $keterangan;
    }

    if (!self::keteranganNeedsMinMaxFormatting($keterangan)) {
      return $keterangan;
    }

    return self::formatRangeLine($min, $max, $equal, null, $kesimpulanBakuMutu, $isNormal) ?? $keterangan;
  }

  public static function matchByGenderFallback($collection, ?string $pasienGender)
  {
    if ($collection->isEmpty()) {
      return null;
    }

    $matched = ($pasienGender !== null && $pasienGender !== '')
      ? $collection->where('gender_baku_mutu', $pasienGender)->first()
      : null;

    if (!$matched) {
      $matched = $collection->whereNull('gender_baku_mutu')->first();
    }

    return $matched ?: $collection->first();
  }

  /**
   * Isi kolom snapshot pada $param.
   *
   * $primaryBakuMutu : satu item (untuk FK baku_mutu + satuan).
   * $allNormal       : Collection semua is_normal yang cocok.
   *                    Jika ≥2 item, min/max/equal/kesimpulan/keterangan dipisah koma.
   *                    Boleh null → pakai $primaryBakuMutu sebagai single-item collection.
   */
  public static function applySnapshotToParameter(PermohonanUjiParameterKlinik $param, $primaryBakuMutu, $allNormal = null, $allBakuMutu = null): void
  {
    if (!$primaryBakuMutu) {
      return;
    }

    // FK dan satuan selalu dari baris utama (satu item)
    if (isset($primaryBakuMutu->unit_id)) {
      $param->satuan_permohonan_uji_parameter_klinik = $primaryBakuMutu->unit_id;
    }
    $param->baku_mutu_permohonan_uji_parameter_klinik = $primaryBakuMutu->id_baku_mutu ?? null;

    if (!self::hasSnapshotColumns()) {
      return;
    }

    // Tentukan koleksi untuk snapshot min/max (sesuai gender/umur pasien)
    if ($allNormal === null) {
      $collection = collect([$primaryBakuMutu]);
    } elseif ($allNormal instanceof Collection) {
      $collection = $allNormal->isNotEmpty() ? $allNormal : collect([$primaryBakuMutu]);
    } else {
      $collection = collect([$allNormal]);
    }

    if ($collection->count() === 1) {
      $item = $collection->first();
      $param->min_baku_mutu_permohonan_uji_parameter_klinik        = $item->min ?? null;
      $param->max_baku_mutu_permohonan_uji_parameter_klinik        = $item->max ?? null;
      $param->equal_baku_mutu_permohonan_uji_parameter_klinik      = $item->equal ?? null;
      $param->kesimpulan_baku_mutu_permohonan_uji_parameter_klinik = $item->kesimpulan_baku_mutu ?? null;
    } else {
      $param->min_baku_mutu_permohonan_uji_parameter_klinik        = self::joinField($collection, 'min');
      $param->max_baku_mutu_permohonan_uji_parameter_klinik        = self::joinField($collection, 'max');
      $param->equal_baku_mutu_permohonan_uji_parameter_klinik      = self::joinField($collection, 'equal');
      $param->kesimpulan_baku_mutu_permohonan_uji_parameter_klinik = self::joinField($collection, 'kesimpulan_baku_mutu');
    }

    // Keterangan dilaporan: mode massal pakai teks bersama L/P; selain itu per baris terpilih
    $massalKeterangan = $allBakuMutu !== null
      ? self::resolveMassalKeteranganDilaporan($allBakuMutu)
      : null;

    if ($massalKeterangan !== null && $massalKeterangan !== '') {
      $param->keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik = $massalKeterangan;
    } elseif ($collection->count() === 1) {
      $param->keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik = self::keteranganDilaporanFromBakuMutuItem($collection->first());
    } else {
      $param->keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik = self::joinKeteranganField($collection);
    }
  }

  /**
   * Nilai keterangan_dilaporan: utamakan nilai_baku_mutu master (teks/HTML),
   * fallback format dari min/max jika nilai_baku_mutu kosong.
   */
  public static function keteranganDilaporanFromBakuMutuItem($item): ?string
  {
    if ($item === null) {
      return null;
    }

    $get = function (string $field) use ($item) {
      if (is_array($item)) {
        return $item[$field] ?? null;
      }

      return $item->$field ?? null;
    };

    $nilai = $get('nilai_baku_mutu');
    if ($nilai !== null && $nilai !== '') {
      return $nilai;
    }

    return self::formatKeteranganFromBakuMutuItem($item);
  }

  private static function joinField(Collection $collection, string $field): ?string
  {
    $values = $collection
      ->map(function ($item) use ($field) {
        $v = $item->$field ?? null;

        return ($v !== null && $v !== '') ? $v : null;
      })
      ->filter()
      ->values()
      ->toArray();

    if (count($values) === 0) {
      return null;
    }

    $unique = array_values(array_unique($values));
    if (count($unique) === 1) {
      return $unique[0];
    }

    if ($field === 'nilai_baku_mutu' && function_exists('decodeNilaiBakuMutuValue')) {
      $byContent = [];
      foreach ($unique as $v) {
        $decoded = decodeNilaiBakuMutuValue($v);
        $key = is_string($decoded)
          ? preg_replace('/\s+/', ' ', trim(strip_tags($decoded)))
          : (string) $v;
        $byContent[$key] = $v;
      }
      if (count($byContent) >= 1) {
        return reset($byContent);
      }
    }

    return implode(', ', $unique);
  }

  private static function joinKeteranganField(Collection $collection): ?string
  {
    $values = $collection
      ->map(function ($item) {
        return self::keteranganDilaporanFromBakuMutuItem($item);
      })
      ->filter(function ($v) {
        return $v !== null && $v !== '';
      })
      ->values()
      ->toArray();

    if (count($values) === 0) {
      return self::joinField($collection, 'nilai_baku_mutu');
    }

    $unique = array_values(array_unique($values));
    if (count($unique) === 1) {
      return $unique[0];
    }

    return implode(', ', $unique);
  }

  private static function normalizeComparable($value): string
  {
    if ($value === null || $value === '') {
      return '';
    }

    return trim((string) $value);
  }

  /**
   * Snapshot min/max/FK selaras dengan baku mutu ter-resolve untuk pasien.
   */
  public static function snapshotMatchesResolved(
    ?string $storedMin,
    ?string $storedMax,
    ?string $storedFkId,
    $resolvedPrimary
  ): bool {
    if (!$resolvedPrimary) {
      return true;
    }

    $resolvedMin = self::normalizeComparable($resolvedPrimary->min ?? null);
    $resolvedMax = self::normalizeComparable($resolvedPrimary->max ?? null);
    $resolvedFk  = self::normalizeComparable($resolvedPrimary->id_baku_mutu ?? null);

    $snapMin = self::normalizeComparable($storedMin);
    $snapMax = self::normalizeComparable($storedMax);
    $snapFk  = self::normalizeComparable($storedFkId);

    if ($snapFk !== '' && $resolvedFk !== '' && $snapFk !== $resolvedFk) {
      return false;
    }

    if ($snapMin !== $resolvedMin || $snapMax !== $resolvedMax) {
      return false;
    }

    return true;
  }

  /**
   * Untuk hasil haji: snapshot dari baku mutu non-haji harus diabaikan.
   */
  public static function shouldForceHajiResolve(
    ?string $storedFkId,
    int $isHaji,
    $resolvedPrimary = null
  ): bool {
    if ($isHaji !== 1) {
      return false;
    }

    if ($resolvedPrimary && (int) ($resolvedPrimary->is_haji ?? 0) === 1) {
      if ($storedFkId === null || $storedFkId === '') {
        return true;
      }
      if (!self::isBakuMutuHaji($storedFkId)) {
        return true;
      }
      $resolvedFk = (string) ($resolvedPrimary->id_baku_mutu ?? '');
      if ($resolvedFk !== '' && (string) $storedFkId !== $resolvedFk) {
        return true;
      }
    }

    return false;
  }

  /**
   * Ambil keterangan_dilaporan untuk tampilan/cetak.
   * Jika snapshot tidak cocok gender/umur pasien, resolve ulang dari master baku mutu.
   */
  public static function resolveKeteranganDilaporanForDisplay(
    ?string $storedKeterangan,
    ?string $storedMin,
    ?string $storedMax,
    ?string $storedFkId,
    string $jenisId,
    string $satuanId,
    ?string $pasienGender,
    $pasienUmur,
    int $isHaji = 0
  ): ?string {
    $gender = self::normalizePasienGender($pasienGender);
    $primary = self::resolveMatchedBakuMutu($jenisId, $satuanId, $gender, $pasienUmur, $isHaji);
    $allBm = self::loadBakuMutuForParameter($jenisId, $satuanId, $isHaji);

    $massalKeterangan = self::resolveMassalKeteranganDilaporan($allBm);
    if ($massalKeterangan !== null && $massalKeterangan !== '') {
      return $massalKeterangan;
    }

    if (!$primary) {
      return $storedKeterangan;
    }

    $forceHaji = self::shouldForceHajiResolve($storedFkId, $isHaji, $primary);

    if (
      !$forceHaji
      && $storedKeterangan !== null
      && $storedKeterangan !== ''
      && $storedKeterangan !== '-'
      && self::snapshotMatchesResolved($storedMin, $storedMax, $storedFkId, $primary)
    ) {
      return $storedKeterangan;
    }

    $allNormal = self::resolveAllNormalMatchedBakuMutu($jenisId, $satuanId, $gender, $pasienUmur, $isHaji);

    if ($allNormal->count() === 1) {
      return self::keteranganDilaporanFromBakuMutuItem($allNormal->first());
    }

    if ($allNormal->isNotEmpty()) {
      return self::joinKeteranganField($allNormal);
    }

    return self::keteranganDilaporanFromBakuMutuItem($primary)
      ?? $storedKeterangan;
  }

  /**
   * Perbaiki snapshot DB jika tidak selaras dengan baku mutu pasien saat ini.
   */
  public static function repairSnapshotIfNeeded(
    PermohonanUjiParameterKlinik $param,
    ?string $pasienGender,
    $pasienUmur,
    int $isHaji = 0
  ): ?string {
    if (!self::hasSnapshotColumns()) {
      return $param->keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik ?? null;
    }

    $gender = self::normalizePasienGender($pasienGender);
    $jenisId = (string) $param->jenis_parameter_klinik_id;
    $satuanId = (string) $param->parameter_satuan_klinik;

    $primary = self::resolveMatchedBakuMutu($jenisId, $satuanId, $gender, $pasienUmur, $isHaji);
    if (!$primary) {
      return $param->keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik ?? null;
    }

    $storedKeterangan = $param->keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik ?? null;
    $storedFk = $param->baku_mutu_permohonan_uji_parameter_klinik ?? null;
    $forceHaji = self::shouldForceHajiResolve($storedFk, $isHaji, $primary);

    if (
      !$forceHaji
      && $storedKeterangan !== null
      && $storedKeterangan !== ''
      && self::snapshotMatchesResolved(
        $param->min_baku_mutu_permohonan_uji_parameter_klinik,
        $param->max_baku_mutu_permohonan_uji_parameter_klinik,
        $storedFk,
        $primary
      )
    ) {
      return $storedKeterangan;
    }

    $allNormal = self::resolveAllNormalMatchedBakuMutu($jenisId, $satuanId, $gender, $pasienUmur, $isHaji);
    $allBm = self::loadBakuMutuForParameter($jenisId, $satuanId, $isHaji);
    self::applySnapshotToParameter($param, $primary, $allNormal, $allBm);
    $param->save();

    return $param->keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik;
  }
}
