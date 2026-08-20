<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Menghitung sampletype_id yang "punya baku mutu" per method+lab untuk UI pemilihan parameter.
 * Semua baris tb_baku_mutu yang valid dihitung, termasuk baku mutu generik untuk
 * Makanan/Minuman/Lainnya tanpa jenis_makanan_id (berlaku untuk seluruh jenis MM).
 */
class BakuMutuSampletypeHelper
{
  /**
   * @return array<string>
   */
  public static function sampletypeIdsWithBakuMutu(string $methodId, string $labId): array
  {
    $ids = DB::table('tb_baku_mutu')
      ->where('method_id', $methodId)
      ->where('lab_id', $labId)
      ->whereNull('deleted_at')
      ->pluck('sampletype_id')
      ->toArray();

    return array_values(array_unique($ids));
  }
}
