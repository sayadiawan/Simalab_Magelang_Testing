<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Backfill step Pendaftaran/Registrasi (id_verification_activity = 1)
 * untuk sampel kesmas yang belum punya baris di tb_verification_activity_samples.
 *
 * start_date / stop_date diambil dari tb_permohonan_uji.date_permohonan_uji
 * (stop = start + 5 menit, mengikuti pola create sample).
 * nama_petugas diambil dari tb_permohonan_uji.petugas_penerima.
 */
class BackfillVerificationActivitySampleRegisterFromPermohonanUji extends Migration
{
    /** Step Pendaftaran / Registrasi */
    const ACTIVITY_REGISTER = 1;

    /** Durasi default step register (menit) */
    const REGISTER_DURATION_MINUTES = 5;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $missing = DB::table('tb_samples as s')
            ->join('tb_permohonan_uji as p', function ($join) {
                $join->on('p.id_permohonan_uji', '=', 's.permohonan_uji_id')
                    ->whereNull('p.deleted_at');
            })
            ->whereNull('s.deleted_at')
            ->whereNotNull('p.date_permohonan_uji')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tb_verification_activity_samples as v')
                    ->whereColumn('v.id_sample', 's.id_samples')
                    ->where('v.id_verification_activity', self::ACTIVITY_REGISTER);
            })
            ->select([
                's.id_samples',
                'p.date_permohonan_uji',
                'p.petugas_penerima',
            ])
            ->orderBy('s.count_id', 'asc')
            ->get();

        if ($missing->isEmpty()) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');
        $rows = [];

        foreach ($missing as $row) {
            $start = $row->date_permohonan_uji;
            $stop = date(
                'Y-m-d H:i:s',
                strtotime($start . ' +' . self::REGISTER_DURATION_MINUTES . ' minutes')
            );
            $petugas = trim((string) ($row->petugas_penerima ?? ''));
            if ($petugas === '') {
                $petugas = 'Petugas';
            }

            $rows[] = [
                'id' => Uuid::uuid4()->toString(),
                'id_verification_activity' => self::ACTIVITY_REGISTER,
                'id_sample' => $row->id_samples,
                'is_klinik' => null,
                'start_date' => $start,
                'stop_date' => $stop,
                'nama_petugas' => mb_substr($petugas, 0, 100),
                'is_done' => 1,
                'resampling' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert per batch agar tidak melebihi max_allowed_packet
            if (count($rows) >= 200) {
                DB::table('tb_verification_activity_samples')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('tb_verification_activity_samples')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Hapus hanya baris register hasil backfill:
     * start_date = date_permohonan_uji dan stop_date = start + 5 menit.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            'DELETE v
             FROM tb_verification_activity_samples AS v
             INNER JOIN tb_samples AS s
               ON s.id_samples = v.id_sample
               AND s.deleted_at IS NULL
             INNER JOIN tb_permohonan_uji AS p
               ON p.id_permohonan_uji = s.permohonan_uji_id
               AND p.deleted_at IS NULL
             WHERE v.id_verification_activity = ?
               AND v.start_date = p.date_permohonan_uji
               AND v.stop_date = DATE_ADD(p.date_permohonan_uji, INTERVAL ? MINUTE)',
            [self::ACTIVITY_REGISTER, self::REGISTER_DURATION_MINUTES]
        );
    }
}
