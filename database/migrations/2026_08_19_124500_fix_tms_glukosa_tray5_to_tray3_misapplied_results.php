<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pindahkan VALUE Glukosa yang salah tempat: dari detail tray 5 ke detail tray 3.
 *
 * Bukan menghapus/mengubah order — hanya memindahkan nilai parameter.
 *
 * Contoh kasus:
 * - Tray 5 (Plasma)     : Glukosa = 88, dieksekusi 12:12:33  → dikosongkan, belum dieksekusi
 * - Tray 3 (Plasma NaF) : Glukosa = kosong, belum dieksekusi → terisi 88, ditandai selesai
 *
 * Rentang waktu dieksekusi sumber: 2026-08-19 12:00:00 s/d 12:14:59
 */
class FixTmsGlukosaTray5ToTray3MisappliedResults extends Migration
{
    private const PARAM_GLUKOSA = 2;

    private const EXECUTED_FROM = '2026-08-19 12:00:00';

    private const EXECUTED_TO = '2026-08-19 12:14:59';

    private const AUDIT_TABLE = 'tb_tms_tray_fix_audit_20260819';

    /**
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_order_tms') || !Schema::hasTable('tb_orderdetail_tms')) {
            return;
        }

        $this->createAuditTable();

        $pairs = $this->findValueTransfers();
        if (empty($pairs)) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($pairs, $now) {
            foreach ($pairs as $pair) {
                DB::table(self::AUDIT_TABLE)->insert([
                    'wrong_order_id' => $pair->from_order_id,
                    'correct_order_id' => $pair->to_order_id,
                    'wrong_detail_id' => $pair->from_detail_id,
                    'correct_detail_id' => $pair->to_detail_id,
                    'move_value' => $pair->move_value,
                    'executed_at' => $pair->source_executed_at,
                    'wrong_old_value' => $pair->move_value,
                    'correct_old_value' => $pair->to_old_value,
                    'wrong_old_is_executed' => (int) ($pair->from_old_is_executed ?? 0),
                    'correct_old_is_executed' => (int) ($pair->to_old_is_executed ?? 0),
                    'wrong_old_executed_at' => $pair->from_old_executed_at,
                    'correct_old_executed_at' => $pair->to_old_executed_at,
                    'created_at' => $now,
                ]);

                // Tray 3: terima nilai dari tray 5
                DB::table('tb_orderdetail_tms')
                    ->where('id_orderdetail_tms', $pair->to_detail_id)
                    ->update([
                        'value' => $pair->move_value,
                        'updated_at' => $now,
                    ]);

                // Tray 5: kosongkan nilai Glukosa
                DB::table('tb_orderdetail_tms')
                    ->where('id_orderdetail_tms', $pair->from_detail_id)
                    ->update([
                        'value' => null,
                        'updated_at' => $now,
                    ]);

                // Tray 3: tandai selesai (pakai waktu eksekusi saat nilai salah masuk ke tray 5)
                DB::table('tb_order_tms')
                    ->where('id_order_tms', $pair->to_order_id)
                    ->update([
                        'is_executed' => 1,
                        'executed_at' => $pair->source_executed_at,
                        'updated_at' => $now,
                    ]);

                // Tray 5: belum dieksekusi jika tidak ada parameter lain yang masih terisi
                if (!$this->orderHasOtherFilledValues($pair->from_order_id, $pair->from_detail_id)) {
                    DB::table('tb_order_tms')
                        ->where('id_order_tms', $pair->from_order_id)
                        ->update([
                            'is_executed' => 0,
                            'executed_at' => null,
                            'updated_at' => $now,
                        ]);
                }
            }
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable(self::AUDIT_TABLE)) {
            return;
        }

        $rows = DB::table(self::AUDIT_TABLE)->orderBy('id')->get();
        if ($rows->isEmpty()) {
            Schema::dropIfExists(self::AUDIT_TABLE);
            return;
        }

        $now = now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($rows, $now) {
            foreach ($rows as $row) {
                DB::table('tb_orderdetail_tms')
                    ->where('id_orderdetail_tms', $row->correct_detail_id)
                    ->update([
                        'value' => $row->correct_old_value,
                        'updated_at' => $now,
                    ]);

                DB::table('tb_orderdetail_tms')
                    ->where('id_orderdetail_tms', $row->wrong_detail_id)
                    ->update([
                        'value' => $row->wrong_old_value,
                        'updated_at' => $now,
                    ]);

                DB::table('tb_order_tms')
                    ->where('id_order_tms', $row->wrong_order_id)
                    ->update([
                        'is_executed' => $row->wrong_old_is_executed,
                        'executed_at' => $row->wrong_old_executed_at,
                        'updated_at' => $now,
                    ]);

                DB::table('tb_order_tms')
                    ->where('id_order_tms', $row->correct_order_id)
                    ->update([
                        'is_executed' => $row->correct_old_is_executed,
                        'executed_at' => $row->correct_old_executed_at,
                        'updated_at' => $now,
                    ]);
            }
        });

        Schema::dropIfExists(self::AUDIT_TABLE);
    }

    /**
     * @return void
     */
    private function createAuditTable()
    {
        if (Schema::hasTable(self::AUDIT_TABLE)) {
            return;
        }

        Schema::create(self::AUDIT_TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('wrong_order_id', 36)->comment('Order tray 5 (sumber value)');
            $table->char('correct_order_id', 36)->comment('Order tray 3 (tujuan value)');
            $table->char('wrong_detail_id', 36);
            $table->char('correct_detail_id', 36);
            $table->text('move_value')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->text('wrong_old_value')->nullable();
            $table->text('correct_old_value')->nullable();
            $table->boolean('wrong_old_is_executed')->default(0);
            $table->boolean('correct_old_is_executed')->default(0);
            $table->timestamp('wrong_old_executed_at')->nullable();
            $table->timestamp('correct_old_executed_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Cari pasangan value Glukosa: terisi di tray 5, kosong di tray 3 (barcode sama).
     *
     * @return array<int, object>
     */
    private function findValueTransfers()
    {
        $paramId = self::PARAM_GLUKOSA;
        $from = self::EXECUTED_FROM;
        $to = self::EXECUTED_TO;

        $sql = "
            SELECT
                ow.id_order_tms              AS from_order_id,
                o3.id_order_tms              AS to_order_id,
                dw.id_orderdetail_tms        AS from_detail_id,
                d3.id_orderdetail_tms        AS to_detail_id,
                dw.value                     AS move_value,
                ow.executed_at               AS source_executed_at,
                d3.value                     AS to_old_value,
                ow.is_executed               AS from_old_is_executed,
                o3.is_executed               AS to_old_is_executed,
                ow.executed_at               AS from_old_executed_at,
                o3.executed_at               AS to_old_executed_at,
                ow.kode_barcode              AS kode_barcode
            FROM tb_order_tms ow
            INNER JOIN tb_orderdetail_tms dw
                ON dw.id_order_tms = ow.id_order_tms
               AND dw.deleted_at IS NULL
               AND dw.id_parameter_tms = ?
            INNER JOIN tb_order_tms o3
                ON o3.kode_barcode = ow.kode_barcode
               AND o3.tray = '3'
               AND o3.deleted_at IS NULL
               AND (o3.is_executed = 0 OR o3.is_executed IS NULL)
               AND o3.created_at < ow.created_at
               AND o3.created_at = (
                   SELECT MIN(o3b.created_at)
                   FROM tb_order_tms o3b
                   INNER JOIN tb_orderdetail_tms d3b
                       ON d3b.id_order_tms = o3b.id_order_tms
                      AND d3b.deleted_at IS NULL
                      AND d3b.id_parameter_tms = ?
                   WHERE o3b.deleted_at IS NULL
                     AND o3b.kode_barcode = ow.kode_barcode
                     AND o3b.tray = '3'
                     AND (o3b.is_executed = 0 OR o3b.is_executed IS NULL)
                     AND o3b.created_at < ow.created_at
                     AND (d3b.value IS NULL OR TRIM(d3b.value) IN ('', '-'))
               )
            INNER JOIN tb_orderdetail_tms d3
                ON d3.id_order_tms = o3.id_order_tms
               AND d3.deleted_at IS NULL
               AND d3.id_parameter_tms = ?
               AND (d3.value IS NULL OR TRIM(d3.value) IN ('', '-'))
            WHERE ow.deleted_at IS NULL
              AND ow.tray = '5'
              AND ow.executed_at BETWEEN ? AND ?
              AND dw.value IS NOT NULL
              AND TRIM(dw.value) NOT IN ('', '-')
            ORDER BY ow.executed_at ASC, ow.kode_barcode ASC
        ";

        return DB::select($sql, [$paramId, $paramId, $paramId, $from, $to]);
    }

    /**
     * Apakah order masih punya parameter lain (selain yang dipindah) yang sudah terisi.
     *
     * @param  string  $orderId
     * @param  string  $excludeDetailId
     * @return bool
     */
    private function orderHasOtherFilledValues($orderId, $excludeDetailId)
    {
        return DB::table('tb_orderdetail_tms')
            ->where('id_order_tms', $orderId)
            ->where('id_orderdetail_tms', '!=', $excludeDetailId)
            ->whereNull('deleted_at')
            ->whereNotNull('value')
            ->whereRaw("TRIM(value) NOT IN ('', '-')")
            ->exists();
    }
}
