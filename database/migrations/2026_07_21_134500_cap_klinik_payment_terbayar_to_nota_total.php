<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaiki data pembayaran klinik yang overpay (Dibayar > Total di nota).
 *
 * Penyebab umum: baris tb_permohonan_uji_payment_klinik dobel
 * (mis. 3x 145.000 → Dibayar 435.000 padahal Total 145.000).
 *
 * Strategi (selaras KlinikPaymentHelper::syncWithTotal):
 * - tagihan = total_harga_permohonan_uji_klinik + biaya_pengambilan_sampel
 * - pertahankan 1 baris pembayaran tertua
 * - set terbayar & total_harga pada baris itu = tagihan
 * - soft-delete baris pembayaran lain pada permohonan yang sama
 * - set status_pembayaran = '1' jika tagihan > 0
 */
class CapKlinikPaymentTerbayarToNotaTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_payment_klinik')
            || !Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        $hasBiayaSampling = Schema::hasColumn('tb_permohonan_uji_klinik_2', 'biaya_pengambilan_sampel');
        $hasStatusBayar = Schema::hasColumn('tb_permohonan_uji_klinik_2', 'status_pembayaran');
        $hasPaymentSoftDelete = Schema::hasColumn('tb_permohonan_uji_payment_klinik', 'deleted_at');

        $tagihanExpr = $hasBiayaSampling
            ? '(IFNULL(k.total_harga_permohonan_uji_klinik, 0) + IFNULL(k.biaya_pengambilan_sampel, 0))'
            : 'IFNULL(k.total_harga_permohonan_uji_klinik, 0)';

        $overpaid = DB::select("
            SELECT
                p.permohonan_uji_klinik_id AS id,
                SUM(p.terbayar_permohonan_uji_payment_klinik) AS sum_terbayar,
                MAX({$tagihanExpr}) AS tagihan
            FROM tb_permohonan_uji_payment_klinik AS p
            INNER JOIN tb_permohonan_uji_klinik_2 AS k
                ON k.id_permohonan_uji_klinik = p.permohonan_uji_klinik_id
            WHERE p.deleted_at IS NULL
              AND k.deleted_at IS NULL
            GROUP BY p.permohonan_uji_klinik_id
            HAVING sum_terbayar > tagihan
               AND tagihan >= 0
        ");

        if (empty($overpaid)) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');

        foreach ($overpaid as $row) {
            $permohonanId = $row->id;
            $tagihan = (int) $row->tagihan;

            $keeper = DB::table('tb_permohonan_uji_payment_klinik')
                ->where('permohonan_uji_klinik_id', $permohonanId)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->orderBy('id_permohonan_uji_payment_klinik', 'asc')
                ->first();

            if (!$keeper) {
                continue;
            }

            DB::table('tb_permohonan_uji_payment_klinik')
                ->where('id_permohonan_uji_payment_klinik', $keeper->id_permohonan_uji_payment_klinik)
                ->update([
                    'terbayar_permohonan_uji_payment_klinik' => max(0, $tagihan),
                    'total_harga_permohonan_uji_payment_klinik' => max(0, $tagihan),
                    'updated_at' => $now,
                ]);

            $extrasQuery = DB::table('tb_permohonan_uji_payment_klinik')
                ->where('permohonan_uji_klinik_id', $permohonanId)
                ->where('id_permohonan_uji_payment_klinik', '!=', $keeper->id_permohonan_uji_payment_klinik)
                ->whereNull('deleted_at');

            if ($hasPaymentSoftDelete) {
                $extrasQuery->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $extrasQuery->delete();
            }

            if ($hasStatusBayar && $tagihan > 0) {
                DB::table('tb_permohonan_uji_klinik_2')
                    ->where('id_permohonan_uji_klinik', $permohonanId)
                    ->whereNull('deleted_at')
                    ->update([
                        'status_pembayaran' => '1',
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Data overpay tidak dikembalikan (soft-delete / cap bersifat one-way).
     *
     * @return void
     */
    public function down()
    {
        // no-op
    }
}
