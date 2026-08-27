<?php

namespace Smt\Masterweb\Traits;

use Carbon\Carbon;

/**
 * Urutan berdasarkan kode sampel: {prefix}/{nomor}/{tahun}
 * Prioritas: lab (opsional) → tahun DESC → nomor sampel DESC
 */
trait SampleCodeOrdering
{
  /** Normalisasi input tanggal filter (Y-m-d atau d/m/Y) */
  protected function normalizeFilterDate($value)
  {
    if ($value === null || $value === '') {
      return null;
    }
    try {
      if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
      }
      return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
    } catch (\Exception $e) {
      return null;
    }
  }

  /**
   * Filter query Sample langsung berdasarkan tb_samples.date_sending.
   */
  protected function applyDateSendingFilterOnSampleQuery($query, $dateStart, $dateEnd, $sampleAlias = 'tb_samples')
  {
    $dateStart = $this->normalizeFilterDate($dateStart);
    $dateEnd = $this->normalizeFilterDate($dateEnd);

    if (!$dateStart && !$dateEnd) {
      return $query;
    }

    $query->whereNotNull("{$sampleAlias}.date_sending");

    if ($dateStart) {
      $query->whereDate("{$sampleAlias}.date_sending", '>=', $dateStart);
    }
    if ($dateEnd) {
      $query->whereDate("{$sampleAlias}.date_sending", '<=', $dateEnd);
    }

    return $query;
  }

  /**
   * Filter permohonan uji klinik berdasarkan tanggal pendaftaran.
   */
  protected function applyDateRegisterFilterOnKlinikQuery($query, $dateStart, $dateEnd, $column = 'tb_permohonan_uji_klinik_2.tglregister_permohonan_uji_klinik')
  {
    $dateStart = $this->normalizeFilterDate($dateStart);
    $dateEnd = $this->normalizeFilterDate($dateEnd);

    if (!$dateStart && !$dateEnd) {
      return $query;
    }

    $query->whereNotNull($column);

    if ($dateStart) {
      $query->whereDate($column, '>=', $dateStart);
    }
    if ($dateEnd) {
      $query->whereDate($column, '<=', $dateEnd);
    }

    return $query;
  }

  /**
   * Filter permohonan yang punya minimal satu sampel dengan date_sending dalam rentang.
   */
  protected function applyDateSendingFilterOnPermohonan($query, $dateStart, $dateEnd)
  {
    $dateStart = $this->normalizeFilterDate($dateStart);
    $dateEnd = $this->normalizeFilterDate($dateEnd);

    if (!$dateStart && !$dateEnd) {
      return $query;
    }

    return $query->whereExists(function ($sub) use ($dateStart, $dateEnd) {
      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
        ->from('tb_samples')
        ->whereColumn('tb_samples.permohonan_uji_id', 'tb_permohonan_uji.id_permohonan_uji')
        ->whereNull('tb_samples.deleted_at')
        ->whereNotNull('tb_samples.date_sending');

      if ($dateStart) {
        $sub->whereDate('tb_samples.date_sending', '>=', $dateStart);
      }
      if ($dateEnd) {
        $sub->whereDate('tb_samples.date_sending', '<=', $dateEnd);
      }
    });
  }
  /**
   * Urutan untuk query Sample (satu baris = satu sampel), mis. halaman verifikasi kesmas.
   */
  protected function applySampleRowListOrdering($query, $sampleAlias = 'tb_samples', $labKodeColumn = null)
  {
    if ($labKodeColumn) {
      $query->orderBy($labKodeColumn, 'ASC');
    }

    $query
      ->orderByRaw(
        "CAST(NULLIF(SUBSTRING_INDEX({$sampleAlias}.codesample_samples, '/', -1), '') AS UNSIGNED) DESC"
      )
      ->orderByRaw(
        "CAST(NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX({$sampleAlias}.codesample_samples, '/', 2), '/', -1), '') AS UNSIGNED) DESC"
      );

    return $query;
  }

  /**
   * Urutan untuk daftar permohonan uji (agregat per permohonan_uji_id).
   * Prioritas: data terbaru di atas supaya input + sample draft baru langsung terlihat.
   */
  protected function applyPermohonanUjiListOrdering($query)
  {
    $sampleSortSub = \Illuminate\Support\Facades\DB::table('tb_samples')
      ->select('permohonan_uji_id')
      ->selectRaw(
        'MAX(CAST(NULLIF(SUBSTRING_INDEX(codesample_samples, "/", -1), "") AS UNSIGNED)) as sort_year'
      )
      ->selectRaw(
        'MAX(CAST(NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(codesample_samples, "/", 2), "/", -1), "") AS UNSIGNED)) as sort_num'
      )
      ->whereNull('deleted_at')
      ->whereNotNull('codesample_samples')
      ->where('codesample_samples', '!=', '')
      ->groupBy('permohonan_uji_id');

    return $query
      ->leftJoinSub($sampleSortSub, 'pu_sample_sort', function ($join) {
        $join->on('pu_sample_sort.permohonan_uji_id', '=', 'tb_permohonan_uji.id_permohonan_uji');
      })
      ->orderBy('tb_permohonan_uji.created_at', 'DESC')
      ->orderByRaw('COALESCE(pu_sample_sort.sort_year, 0) DESC')
      ->orderByRaw('COALESCE(pu_sample_sort.sort_num, 0) DESC');
  }
}
