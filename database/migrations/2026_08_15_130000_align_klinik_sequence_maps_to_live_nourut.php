<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Rapikan mapping nomor klinik 2026 ke nomor spesimen hidup (nourut).
 *
 * Setelah restore nomor asli, masih ada:
 * - duplikat global_lab_sequence_detail per permohonan (satu nomor asli, satu sisa resequence)
 * - tb_number_klinik yang belum ikut nomor asli
 * - permohonan tanpa baris sequence_detail
 *
 * Idempotent. Tidak mengubah nourut/noregister/nomer_lab.
 */
class AlignKlinikSequenceMapsToLiveNourut extends Migration
{
    const YEAR = 2026;
    const DEFAULT_KLINIK_LAB_ID = 'bbed2259-2826-4711-b0fc-abdad5aace22';

    public function up(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        $this->dedupeAndAlignSequenceDetails();
        $this->insertMissingSequenceDetails();
        $this->syncNumberKlinik();
        $this->syncSequenceCounter();
    }

    public function down(): void
    {
        // Tidak di-rollback otomatis.
    }

    private function liveKlinikRows()
    {
        return DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->whereYear('created_at', self::YEAR)
            ->where(function ($q) {
                $q->where('is_nomor_spesimen_manual', 0)
                    ->orWhereNull('is_nomor_spesimen_manual');
            })
            ->whereRaw("CAST(nourut_permohonan_uji_klinik AS UNSIGNED) > 0")
            ->get([
                'id_permohonan_uji_klinik',
                'nourut_permohonan_uji_klinik',
            ]);
    }

    private function nourutOf($row): int
    {
        return (int) preg_replace('/\D+/', '', trim((string) ($row->nourut_permohonan_uji_klinik ?? '')));
    }

    private function dedupeAndAlignSequenceDetails(): void
    {
        if (!Schema::hasTable('global_lab_sequence_detail')) {
            return;
        }

        $now = now();

        foreach ($this->liveKlinikRows() as $row) {
            $id = (string) $row->id_permohonan_uji_klinik;
            $nourut = $this->nourutOf($row);
            if ($nourut < 1) {
                continue;
            }

            $own = DB::table('global_lab_sequence_detail')
                ->where('lab_type', 'klinik')
                ->where('reference_id', $id)
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(['id', 'sequence_number']);

            if ($own->isEmpty()) {
                continue;
            }

            $keeper = $own->first(function ($detail) use ($nourut) {
                return (int) $detail->sequence_number === $nourut;
            }) ?: $own->first();

            $extras = $own->filter(function ($detail) use ($keeper) {
                return $detail->id !== $keeper->id;
            })->pluck('id')->all();

            if (!empty($extras)) {
                DB::table('global_lab_sequence_detail')
                    ->whereIn('id', $extras)
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);
            }

            if ((int) $keeper->sequence_number === $nourut) {
                continue;
            }

            $occupant = DB::table('global_lab_sequence_detail')
                ->where('year', self::YEAR)
                ->where('sequence_number', $nourut)
                ->whereNull('deleted_at')
                ->where('id', '<>', $keeper->id)
                ->first(['id', 'lab_type', 'reference_id']);

            if ($occupant) {
                $otherLiveKlinik = $occupant->lab_type === 'klinik'
                    && $occupant->reference_id
                    && $occupant->reference_id !== $id
                    && DB::table('tb_permohonan_uji_klinik_2')
                        ->where('id_permohonan_uji_klinik', $occupant->reference_id)
                        ->whereNull('deleted_at')
                        ->exists();

                if ($otherLiveKlinik) {
                    continue;
                }

                DB::table('global_lab_sequence_detail')
                    ->where('id', $occupant->id)
                    ->update(['deleted_at' => $now, 'updated_at' => $now]);
            }

            DB::table('global_lab_sequence_detail')
                ->where('id', $keeper->id)
                ->update([
                    'sequence_number' => $nourut,
                    'year' => self::YEAR,
                    'updated_at' => $now,
                ]);
        }
    }

    private function insertMissingSequenceDetails(): void
    {
        if (!Schema::hasTable('global_lab_sequence_detail')) {
            return;
        }

        $now = now();
        $labId = self::DEFAULT_KLINIK_LAB_ID;

        foreach ($this->liveKlinikRows() as $row) {
            $id = (string) $row->id_permohonan_uji_klinik;
            $nourut = $this->nourutOf($row);
            if ($nourut < 1) {
                continue;
            }

            $hasOwn = DB::table('global_lab_sequence_detail')
                ->where('lab_type', 'klinik')
                ->where('reference_id', $id)
                ->whereNull('deleted_at')
                ->exists();
            if ($hasOwn) {
                continue;
            }

            $occupant = DB::table('global_lab_sequence_detail')
                ->where('year', self::YEAR)
                ->where('sequence_number', $nourut)
                ->whereNull('deleted_at')
                ->first(['id', 'lab_type', 'reference_id']);

            if ($occupant) {
                $otherLiveKlinik = $occupant->lab_type === 'klinik'
                    && !empty($occupant->reference_id)
                    && $occupant->reference_id !== $id
                    && DB::table('tb_permohonan_uji_klinik_2')
                        ->where('id_permohonan_uji_klinik', $occupant->reference_id)
                        ->whereNull('deleted_at')
                        ->exists();

                if ($otherLiveKlinik) {
                    continue;
                }

                DB::table('global_lab_sequence_detail')
                    ->where('id', $occupant->id)
                    ->update([
                        'lab_type' => 'klinik',
                        'lab_id' => $labId,
                        'reference_id' => $id,
                        'sequence_number' => $nourut,
                        'year' => self::YEAR,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            $revived = DB::table('global_lab_sequence_detail')
                ->where('year', self::YEAR)
                ->where('sequence_number', $nourut)
                ->whereNotNull('deleted_at')
                ->orderByDesc('updated_at')
                ->value('id');

            if ($revived) {
                DB::table('global_lab_sequence_detail')
                    ->where('id', $revived)
                    ->update([
                        'lab_type' => 'klinik',
                        'lab_id' => $labId,
                        'reference_id' => $id,
                        'sequence_number' => $nourut,
                        'year' => self::YEAR,
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            DB::table('global_lab_sequence_detail')->insert([
                'id' => (string) Str::uuid(),
                'year' => self::YEAR,
                'sequence_number' => $nourut,
                'lab_id' => $labId,
                'lab_type' => 'klinik',
                'reference_id' => $id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function syncNumberKlinik(): void
    {
        if (!Schema::hasTable('tb_number_klinik')) {
            return;
        }

        DB::update("
            UPDATE tb_number_klinik n
            JOIN tb_permohonan_uji_klinik_2 p
              ON p.id_permohonan_uji_klinik = n.id_permohonan_uji_klinik
             AND p.deleted_at IS NULL
            SET
              n.new_number = LPAD(CAST(p.nourut_permohonan_uji_klinik AS UNSIGNED), 4, '0'),
              n.last_number = LPAD(CAST(p.nourut_permohonan_uji_klinik AS UNSIGNED), 4, '0'),
              n.updated_at = NOW()
            WHERE n.deleted_at IS NULL
              AND YEAR(p.created_at) = ?
              AND (p.is_nomor_spesimen_manual = 0 OR p.is_nomor_spesimen_manual IS NULL)
              AND CAST(p.nourut_permohonan_uji_klinik AS UNSIGNED) > 0
              AND (
                CAST(IFNULL(n.new_number, '0') AS UNSIGNED) <> CAST(p.nourut_permohonan_uji_klinik AS UNSIGNED)
                OR CAST(IFNULL(n.last_number, '0') AS UNSIGNED) <> CAST(p.nourut_permohonan_uji_klinik AS UNSIGNED)
              )
        ", [self::YEAR]);

        DB::update("
            UPDATE tb_number_klinik n
            JOIN tb_permohonan_uji_klinik_2 p
              ON p.id_permohonan_uji_klinik = n.id_permohonan_uji_klinik
             AND p.deleted_at IS NULL
            SET
              n.new_number = LPAD(CAST(TRIM(p.nomor_spesimen_manual) AS UNSIGNED), 4, '0'),
              n.last_number = LPAD(CAST(TRIM(p.nomor_spesimen_manual) AS UNSIGNED), 4, '0'),
              n.updated_at = NOW()
            WHERE n.deleted_at IS NULL
              AND YEAR(p.created_at) = ?
              AND p.is_nomor_spesimen_manual = 1
              AND TRIM(IFNULL(p.nomor_spesimen_manual, '')) REGEXP '^[0-9]+$'
              AND CAST(TRIM(p.nomor_spesimen_manual) AS UNSIGNED) > 0
              AND (
                CAST(IFNULL(n.new_number, '0') AS UNSIGNED) <> CAST(TRIM(p.nomor_spesimen_manual) AS UNSIGNED)
                OR CAST(IFNULL(n.last_number, '0') AS UNSIGNED) <> CAST(TRIM(p.nomor_spesimen_manual) AS UNSIGNED)
              )
        ", [self::YEAR]);
    }

    private function syncSequenceCounter(): void
    {
        if (!Schema::hasTable('global_lab_sequence')) {
            return;
        }

        $maxKlinik = (int) DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->whereYear('created_at', self::YEAR)
            ->max(DB::raw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED)'));

        $maxSample = 0;
        if (Schema::hasTable('tb_samples')) {
            $maxSample = (int) DB::table('tb_samples')
                ->whereNull('deleted_at')
                ->whereYear('created_at', self::YEAR)
                ->max('count_id');
        }

        $target = max($maxKlinik, $maxSample);
        if ($target < 1) {
            return;
        }

        DB::table('global_lab_sequence')
            ->where('year', self::YEAR)
            ->whereNull('deleted_at')
            ->where('last_number', '<', $target)
            ->update([
                'last_number' => $target,
                'updated_at' => now(),
            ]);
    }
}
