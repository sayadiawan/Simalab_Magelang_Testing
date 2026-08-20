<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Support\Collection;
use Smt\Masterweb\Models\ParameterCategoryLayout;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;

/**
 * Helper paket untuk alur Haji — disamakan dengan pemeriksaan umum (paket individu di layout).
 * Tidak bergantung pada master komposit "Paket Haji".
 */
class HajiPaketHelper
{
  /**
   * Paket individu aktif di category layout (bukan BPJS/Klaim, bukan "Paket Haji").
   *
   * @return Collection|ParameterPaketKlinik[]
   */
  public static function getLayoutIndividualPakets(): Collection
  {
    static $cached = null;

    if ($cached !== null) {
      return $cached;
    }

    $categoryLayouts = ParameterCategoryLayout::where('is_active', 1)
      ->orderBy('sort_order', 'asc')
      ->with(['categoryItems' => function ($query) {
        $query->with(['parameterPaketKlinik.parameterpaketjenisklinik.parametersatuanpaketklinik.parametersatuanklinik'])
          ->orderBy('row_position', 'asc')
          ->orderBy('column_position', 'asc')
          ->orderBy('sort_order', 'asc');
      }])
      ->get();

    $matched = collect();
    foreach ($categoryLayouts as $category) {
      foreach ($category->categoryItems as $item) {
        if (!$item->parameterPaketKlinik) {
          continue;
        }

        $paket = $item->parameterPaketKlinik;
        $paketName = trim((string) $paket->name_parameter_paket_klinik);
        if ($paketName === '' || self::isCompositePaketHajiName($paketName)) {
          continue;
        }

        if (self::isBillingVariantPaketName($paketName)) {
          continue;
        }

        $matched->put($paket->id_parameter_paket_klinik, $paket);
      }
    }

    $cached = $matched->values();

    return $cached;
  }

  /**
   * Map satuan yang sudah tersimpan di permohonan → paket individu layout
   * (untuk legacy data yang masih menyimpan baris komposit "Paket Haji").
   *
   * @param string[] $satuanIds
   * @return Collection|ParameterPaketKlinik[]
   */
  public static function resolveLayoutPaketsFromSatuanIds(array $satuanIds): Collection
  {
    $satuanIds = collect($satuanIds)->filter()->unique()->values();
    if ($satuanIds->isEmpty()) {
      return collect();
    }

    $satuanIdSet = $satuanIds->flip();

    $nameById = ParameterSatuanKlinik::whereIn('id_parameter_satuan_klinik', $satuanIds->all())
      ->whereNull('deleted_at')
      ->pluck('name_parameter_satuan_klinik', 'id_parameter_satuan_klinik');

    $savedNames = $nameById->map(function ($name) {
      return self::canonicalSatuanName($name);
    })->filter()->unique()->values();

    $matched = collect();
    foreach (self::getLayoutIndividualPakets() as $paket) {
      $individualSatuanIds = collect();
      $individualNames = collect();
      foreach ($paket->parameterpaketjenisklinik as $jenis) {
        foreach ($jenis->parametersatuanpaketklinik as $satuan) {
          if (!empty($satuan->parameter_satuan_klinik)) {
            $individualSatuanIds->push($satuan->parameter_satuan_klinik);
          }
          $canonical = self::canonicalSatuanName(optional($satuan->parametersatuanklinik)->name_parameter_satuan_klinik);
          if ($canonical !== '') {
            $individualNames->push($canonical);
          }
        }
      }

      $individualSatuanIds = $individualSatuanIds->unique()->values();
      if ($individualSatuanIds->isEmpty()) {
        continue;
      }

      $idOverlap = $individualSatuanIds->first(function ($id) use ($satuanIdSet) {
        return isset($satuanIdSet[$id]);
      });

      if ($idOverlap || self::satuanNamesOverlapHaji($individualNames, $savedNames)) {
        $matched->put($paket->id_parameter_paket_klinik, $paket);
      }
    }

    return $matched->values();
  }

  /**
   * Resolve paket individu dari permohonan (expand legacy Paket Haji bila perlu).
   *
   * @return Collection|ParameterPaketKlinik[]
   */
  public static function resolveLayoutPaketsFromPermohonan(PermohonanUjiKlinik2 $permohonan): Collection
  {
    $permohonan->loadMissing(['permohonanujipaketklinik', 'permohonanujiparameterklinik']);

    $direct = collect();
    $hasComposite = false;

    foreach ($permohonan->permohonanujipaketklinik as $row) {
      if (!empty($row->parameter_paket_extra) || empty($row->parameter_paket_klinik)) {
        continue;
      }

      $paket = ParameterPaketKlinik::find($row->parameter_paket_klinik);
      if (!$paket) {
        continue;
      }

      if (self::isCompositePaketHajiName($paket->name_parameter_paket_klinik)) {
        $hasComposite = true;
        continue;
      }

      if (self::isBillingVariantPaketName($paket->name_parameter_paket_klinik)) {
        continue;
      }

      $direct->put($paket->id_parameter_paket_klinik, $paket);
    }

    if (!$hasComposite) {
      return $direct->values();
    }

    $satuanIds = $permohonan->permohonanujiparameterklinik
      ->pluck('parameter_satuan_klinik')
      ->filter()
      ->unique()
      ->values()
      ->all();

    $fromSatuan = self::resolveLayoutPaketsFromSatuanIds($satuanIds);

    return $direct->merge($fromSatuan->keyBy('id_parameter_paket_klinik'))->values();
  }

  /**
   * Bentuk struktur session/form jenis_parameters dari collection paket.
   */
  public static function buildJenisParametersSession(Collection $pakets): array
  {
    $parameters = [];
    foreach ($pakets as $paket) {
      $paketId = $paket->id_parameter_paket_klinik;
      $harga = (int) ($paket->harga_parameter_paket_klinik ?? 0);
      $parameters[$paketId] = [
        'pakets' => [
          $paketId . '_' . $harga,
        ],
      ];
    }

    return $parameters;
  }

  /**
   * Paket individu yang paling sering (mayoritas) dipakai pasien dalam satu batch haji.
   * Dihitung dari kombinasi paket tersimpan; jika tidak ada pasien, kembalikan collection kosong.
   *
   * @return Collection|ParameterPaketKlinik[]
   */
  public static function resolveMajorityLayoutPaketsForHaji(string $hajiId): Collection
  {
    $permohonanList = PermohonanUjiKlinik2::query()
      ->where('id_permohonan_uji_klinik_haji', $hajiId)
      ->whereNull('deleted_at')
      ->with(['permohonanujipaketklinik', 'permohonanujiparameterklinik'])
      ->get();

    return self::resolveMajorityLayoutPaketsFromPermohonanList($permohonanList);
  }

  /**
   * Paket individu mayoritas di semua customer haji yang sudah ada
   * (fallback saat batch baru belum punya pasien).
   *
   * @return Collection|ParameterPaketKlinik[]
   */
  public static function resolveMajorityLayoutPaketsFromExistingHaji(?string $excludeHajiId = null): Collection
  {
    $query = PermohonanUjiKlinik2::query()
      ->whereNotNull('id_permohonan_uji_klinik_haji')
      ->whereNull('deleted_at')
      ->with(['permohonanujipaketklinik', 'permohonanujiparameterklinik']);

    if (!empty($excludeHajiId)) {
      $query->where('id_permohonan_uji_klinik_haji', '!=', $excludeHajiId);
    }

    // Batasi sample agar ringan: cukup mewakili kombinasi paket.
    $permohonanList = $query
      ->orderBy('created_at', 'desc')
      ->limit(300)
      ->get();

    return self::resolveMajorityLayoutPaketsFromPermohonanList($permohonanList);
  }

  /**
   * Ambil kombinasi paket yang paling sering muncul di daftar permohonan.
   *
   * @param  \Illuminate\Support\Collection|array  $permohonanList
   * @return Collection|ParameterPaketKlinik[]
   */
  public static function resolveMajorityLayoutPaketsFromPermohonanList($permohonanList): Collection
  {
    $permohonanList = collect($permohonanList);
    if ($permohonanList->isEmpty()) {
      return collect();
    }

    $signatureCounts = [];
    $signaturePakets = [];

    foreach ($permohonanList as $permohonan) {
      $pakets = self::resolveLayoutPaketsFromPermohonan($permohonan);
      if ($pakets->isEmpty()) {
        continue;
      }

      $ids = $pakets->pluck('id_parameter_paket_klinik')->unique()->sort()->values()->all();
      $signature = implode('|', $ids);
      if ($signature === '') {
        continue;
      }

      if (!isset($signatureCounts[$signature])) {
        $signatureCounts[$signature] = 0;
        $signaturePakets[$signature] = $pakets->keyBy('id_parameter_paket_klinik');
      }
      $signatureCounts[$signature]++;
    }

    if (empty($signatureCounts)) {
      return collect();
    }

    arsort($signatureCounts);
    $topSignature = array_key_first($signatureCounts);

    return collect($signaturePakets[$topSignature] ?? [])->values();
  }

  /**
   * @deprecated Gunakan getLayoutIndividualPakets / resolveLayoutPaketsFromPermohonan.
   * Tetap ada agar pemanggilan lama tidak fatal; tidak lagi membaca master Paket Haji.
   */
  public static function getIndividualPaketsMatchingPaketHaji()
  {
    return self::getLayoutIndividualPakets();
  }

  /**
   * @deprecated Harga mengikuti paket individu yang dipilih; master Paket Haji diabaikan.
   */
  public static function sumIndividualPaketHajiHarga(): int
  {
    $sum = 0;
    foreach (self::getLayoutIndividualPakets() as $paket) {
      $sum += (int) ($paket->harga_parameter_paket_klinik ?? 0);
    }

    return $sum;
  }

  /**
   * @deprecated
   */
  public static function resolvePaketHajiStoredHarga(?ParameterPaketKlinik $dataPaket): int
  {
    if ($dataPaket && !self::isCompositePaketHajiName($dataPaket->name_parameter_paket_klinik ?? '')) {
      $master = (int) ($dataPaket->harga_parameter_paket_klinik ?? 0);
      if ($master > 0) {
        return $master;
      }
    }

    return 0;
  }

  public static function isCompositePaketHajiName(?string $paketName): bool
  {
    return strtolower(trim((string) $paketName)) === 'paket haji';
  }

  public static function isBillingVariantPaketName(string $paketName): bool
  {
    return (bool) preg_match('/\(\s*(bpjs|klaim)\s*\)/i', $paketName)
      || (bool) preg_match('/\b(bpjs|klaim)\b/i', $paketName);
  }

  public static function canonicalSatuanName(?string $name): string
  {
    $normalized = trim(preg_replace('/\s+/u', ' ', mb_strtolower((string) $name, 'UTF-8')));
    if ($normalized === '') {
      return '';
    }

    $normalized = trim(preg_replace('/\s*\(.*?\)\s*/u', ' ', $normalized));
    $normalized = trim(preg_replace('/\s+/u', ' ', $normalized));

    if ($normalized === 'led' || strpos($normalized, 'laju endap') === 0 || strpos($normalized, 'led ') === 0) {
      return 'laju endap darah';
    }

    if (in_array($normalized, ['creatinine', 'kreatinin', 'creatinin'], true)) {
      return 'kreatinin';
    }

    if (in_array($normalized, ['triglyceride', 'trigliserid', 'trigliseride', 'trigliserida'], true)) {
      return 'triglyceride';
    }

    return $normalized;
  }

  /**
   * @param \Illuminate\Support\Collection|array $individualNames
   * @param \Illuminate\Support\Collection|array $hajiNames
   */
  public static function satuanNamesOverlapHaji($individualNames, $hajiNames): bool
  {
    $individualNames = collect($individualNames)->filter()->values();
    $hajiNames = collect($hajiNames)->filter()->values();

    foreach ($individualNames as $ind) {
      foreach ($hajiNames as $haji) {
        if ($ind === $haji) {
          return true;
        }
        if ($ind !== '' && $haji !== '' && (strpos($haji, $ind) !== false || strpos($ind, $haji) !== false)) {
          return true;
        }
      }
    }

    return false;
  }
}
