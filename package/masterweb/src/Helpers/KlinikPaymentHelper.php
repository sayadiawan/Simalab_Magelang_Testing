<?php

namespace Smt\Masterweb\Helpers;

use Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;

class KlinikPaymentHelper
{
  /**
   * Sinkronkan record pembayaran klinik dengan total tagihan terkini.
   * Setelah edit parameter (total turun), cap terbayar agar tidak melebihi total baru
   * sehingga kolom Dibayar di nota tidak lebih besar dari Total.
   *
   * @return array{sudah_dibayar:int,sisa_tagihan:int}
   */
  public static function syncWithTotal($permohonanUjiKlinikId, int $totalAkhir): array
  {
    $existingPayment = PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $permohonanUjiKlinikId)
      ->whereNull('deleted_at')
      ->orderBy('created_at', 'asc')
      ->first();

    $sudah_dibayar = (int) PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $permohonanUjiKlinikId)
      ->whereNull('deleted_at')
      ->sum('terbayar_permohonan_uji_payment_klinik');

    if ($existingPayment) {
      $existingPayment->total_harga_permohonan_uji_payment_klinik = $totalAkhir;

      if ($sudah_dibayar > $totalAkhir) {
        $existingPayment->terbayar_permohonan_uji_payment_klinik = max(0, $totalAkhir);
        $sudah_dibayar = max(0, $totalAkhir);

        PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $permohonanUjiKlinikId)
          ->where('id_permohonan_uji_payment_klinik', '!=', $existingPayment->id_permohonan_uji_payment_klinik)
          ->whereNull('deleted_at')
          ->update(['deleted_at' => now()]);
      }

      $existingPayment->save();
    } elseif ($sudah_dibayar > $totalAkhir) {
      $sudah_dibayar = max(0, $totalAkhir);
    }

    return [
      'sudah_dibayar' => $sudah_dibayar,
      'sisa_tagihan' => max(0, $totalAkhir - $sudah_dibayar),
    ];
  }
}
