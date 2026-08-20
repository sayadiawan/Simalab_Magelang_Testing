<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use Smt\Masterweb\Models\NomerLabKesmas;
use Smt\Masterweb\Helpers\IssuedNumber;

/**
 * Model untuk sekuens Nomer Lab.
 *
 * Nomer Lab lahir setelah pekerjaan selesai:
 *  - Klinik  : ketika VerificationActivitySample step 5 (validasi) is_done = 1
 *  - Kesmas  : ketika SEMUA sampel dalam satu PermohonanUji sudah step 5 is_done = 1
 *
 * Tabel: global_nomer_lab_sequence
 */
class NomerLabSequence extends Model
{
    protected $table      = 'global_nomer_lab_sequence';
    protected $primaryKey = 'id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['id', 'year', 'last_number'];

    /**
     * Max nomor lab yang sudah terpakai (kesmas + klinik) untuk tahun ini.
     */
    public static function resolveMaxIssuedLabNumber(int $year = null): int
    {
        return self::resolveMaxIssuedLabNumberFromLiveData($year);
    }

    /**
     * Max nomor lab dari data hidup kesmas + klinik (tanpa tb_lab_num / counter junk).
     */
    public static function resolveMaxIssuedLabNumberFromLiveData(int $year = null): int
    {
        if ($year === null) {
            $year = (int) date('Y');
        }

        $nums = [];

        try {
            if (Schema::hasTable('tb_nomer_lab_kesmas')) {
                $q = DB::table('tb_nomer_lab_kesmas')->where('year', $year);
                if (Schema::hasColumn('tb_nomer_lab_kesmas', 'deleted_at')) {
                    $q->whereNull('deleted_at');
                }
                foreach ($q->pluck('nomer_lab') as $n) {
                    $nums[] = (int) $n;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
                $klinikYear = function ($q) use ($year) {
                    $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                        ->orWhere(function ($q2) use ($year) {
                            $q2->whereNull('tglregister_permohonan_uji_klinik')
                                ->whereYear('created_at', $year);
                        });
                };

                $cols = ['nomor_lab_manual'];
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
                    $cols[] = 'nomer_lab';
                }

                $rows = DB::table('tb_permohonan_uji_klinik_2')
                    ->whereNull('deleted_at')
                    ->where($klinikYear)
                    ->get($cols);

                foreach ($rows as $row) {
                    $manual = preg_replace('/\D+/', '', trim((string) ($row->nomor_lab_manual ?? '')));
                    if ($manual !== '' && strlen($manual) <= 6) {
                        $nums[] = (int) $manual;
                    }
                    if (isset($row->nomer_lab)) {
                        $nomer = preg_replace('/\D+/', '', trim((string) $row->nomer_lab));
                        if ($nomer !== '' && strlen($nomer) <= 6) {
                            $nums[] = (int) $nomer;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return IssuedNumber::maxDense($nums);
    }

    /**
     * Naikkan last_number sequence lab minimal ke $minNumber (setelah input manual).
     * Scan data hidup di luar lock; bump counter singkat di koneksi seq.
     */
    public static function raiseLastNumberToAtLeast(int $minNumber, int $year = null): void
    {
        if ($minNumber < 1 || $minNumber > 999999) {
            return;
        }

        if ($year === null) {
            $year = (int) date('Y');
        }

        $live = self::resolveMaxIssuedLabNumberFromLiveData($year);
        if ($live > 0 && $minNumber > ($live + 100)) {
            return;
        }

        self::bumpLastNumberToAtLeast(max($live, $minNumber), $year);
    }

    /**
     * Setelah hapus klinik: samakan counter lab ke max nomor hidup (boleh turun).
     * Scan di luar lock; update counter di koneksi seq (jangan tahan lock di transaksi hapus).
     */
    public static function syncLastNumberFromLiveData(int $year = null): void
    {
        if ($year === null) {
            $year = (int) date('Y');
        }

        $maxLive = self::resolveMaxIssuedLabNumberFromLiveData($year);
        $conn = \Smt\Masterweb\Helpers\SequenceDb::connectionName();

        DB::connection($conn)->transaction(function () use ($conn, $year, $maxLive) {
            $seq = self::on($conn)->where('year', $year)->lockForUpdate()->first();
            if (!$seq) {
                self::on($conn)->create([
                    'id'          => Uuid::uuid4()->toString(),
                    'year'        => $year,
                    'last_number' => $maxLive,
                ]);

                return;
            }

            if ((int) $seq->last_number !== $maxLive) {
                $seq->last_number = $maxLive;
                $seq->save();
            }
        });
    }

    /**
     * Bump last_number singkat tanpa scan data hidup.
     */
    private static function bumpLastNumberToAtLeast(int $target, int $year): void
    {
        if ($target < 1) {
            return;
        }

        $conn = \Smt\Masterweb\Helpers\SequenceDb::connectionName();
        DB::connection($conn)->transaction(function () use ($conn, $year, $target) {
            $seq = self::on($conn)->where('year', $year)->lockForUpdate()->first();
            if (!$seq) {
                self::on($conn)->create([
                    'id'          => Uuid::uuid4()->toString(),
                    'year'        => $year,
                    'last_number' => $target,
                ]);

                return;
            }

            if ((int) $seq->last_number < $target) {
                $seq->last_number = $target;
                $seq->save();
            }
        });
    }

    /**
     * Lihat nomor lab berikutnya TANPA mengincrementnya (untuk preview di UI).
     * Gabungan kesmas + klinik.
     */
    public static function peekNextNumber(int $year = null): int
    {
        return self::resolveMaxIssuedLabNumber($year) + 1;
    }

    /**
     * Ambil nomor lab berikutnya (thread-safe), mengikuti max kesmas + klinik.
     * Scan data hidup di luar lock; bump counter singkat di koneksi seq.
     */
    public static function getNextNumber(int $year = null): int
    {
        if ($year === null) {
            $year = (int) date('Y');
        }

        return self::resolveUniqueLabNumber(null, $year);
    }

    /**
     * Cek apakah nomor lab sudah dipakai di kesmas atau klinik (tahun terkait).
     */
    public static function isLabNumberTaken(int $number, int $year = null, ?string $excludeKlinikId = null): bool
    {
        if ($number < 1) {
            return false;
        }

        if ($year === null) {
            $year = (int) date('Y');
        }

        $asString = (string) $number;

        try {
            if (Schema::hasTable('tb_nomer_lab_kesmas')) {
                $q = DB::table('tb_nomer_lab_kesmas')->where('year', $year)->where('nomer_lab', $number);
                if (Schema::hasColumn('tb_nomer_lab_kesmas', 'deleted_at')) {
                    $q->whereNull('deleted_at');
                }
                if ($q->exists()) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
                $klinikYear = function ($q) use ($year) {
                    $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                        ->orWhere(function ($q2) use ($year) {
                            $q2->whereNull('tglregister_permohonan_uji_klinik')
                                ->whereYear('created_at', $year);
                        });
                };

                $q = DB::table('tb_permohonan_uji_klinik_2')
                    ->whereNull('deleted_at')
                    ->where($klinikYear)
                    ->where(function ($q) use ($number, $asString) {
                        $q->where('nomor_lab_manual', $asString);
                        if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
                            $q->orWhere('nomer_lab', $number)
                                ->orWhere('nomer_lab', $asString);
                        }
                    });

                if ($excludeKlinikId) {
                    $q->where('id_permohonan_uji_klinik', '!=', $excludeKlinikId);
                }

                if ($q->exists()) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return false;
    }

    /**
     * Ambil nomor lab unik.
     * Scan/cek data hidup di luar lock; bump counter singkat di koneksi seq.
     * $preferred kosong/0/bentrok → naik ke slot kosong (hormati $minExclusive untuk batch).
     */
    public static function resolveUniqueLabNumber(
        $preferred = null,
        int $year = null,
        ?string $excludeKlinikId = null,
        ?int $minExclusive = null
    ): int {
        if ($year === null) {
            $year = (int) date('Y');
        }

        $preferred = (int) preg_replace('/\D+/', '', (string) ($preferred ?? ''));
        if ($preferred < 1) {
            $preferred = 0;
        }

        $conn = \Smt\Masterweb\Helpers\SequenceDb::connectionName();
        $attempts = 0;
        $candidate = 0;

        while ($attempts < 15) {
            $attempts++;
            $maxLive = self::resolveMaxIssuedLabNumberFromLiveData($year);
            $stored = (int) (self::where('year', $year)->value('last_number') ?? 0);
            if ($stored > 999999 || ($maxLive > 0 && $stored > ($maxLive + 100))) {
                $stored = 0;
            }

            $candidate = $preferred > 0 ? $preferred : (max($stored, $maxLive) + 1);
            if ($minExclusive !== null) {
                $candidate = max($candidate, ((int) $minExclusive) + 1);
            }

            $guard = 0;
            while ($guard < 10000 && self::isLabNumberTaken($candidate, $year, $excludeKlinikId)) {
                $candidate++;
                $guard++;
            }
            $candidate = max(1, $candidate);

            try {
                $final = (int) DB::connection($conn)->transaction(function () use ($conn, $year, $candidate, $preferred) {
                    $seq = self::on($conn)->where('year', $year)->lockForUpdate()->first();
                    if (!$seq) {
                        self::on($conn)->create([
                            'id'          => Uuid::uuid4()->toString(),
                            'year'        => $year,
                            'last_number' => 0,
                        ]);
                        $seq = self::on($conn)->where('year', $year)->lockForUpdate()->first();
                    }

                    $final = $candidate;
                    if ($preferred < 1) {
                        $final = max($candidate, ((int) $seq->last_number) + 1);
                    }

                    if ((int) $seq->last_number < $final) {
                        $seq->last_number = $final;
                        $seq->save();
                    }

                    return $final;
                });

                if ($preferred < 1 && self::isLabNumberTaken($final, $year, $excludeKlinikId)) {
                    $minExclusive = $final;
                    $preferred = 0;
                    continue;
                }

                return $final;
            } catch (\Throwable $e) {
                if (!\Smt\Masterweb\Helpers\SequenceDb::isRetryableSequenceException($e) || $attempts >= 15) {
                    throw $e;
                }
                usleep(50000 * $attempts);
            }
        }

        throw new \RuntimeException('Gagal resolve nomor lab unik setelah beberapa percobaan.');
    }

    /**
     * Assign nomer_lab ke klinik jika belum ada.
     * Dipanggil setelah step 5 (validasi) is_done = 1.
     *
     * Jika is_nomor_lab_manual = 1, pakai nomor_lab_manual (jangan generate otomatis).
     *
     * @param  string  $idPermohonanUjiKlinik
     * @return int|null  Nomer lab yang ditetapkan, atau null jika sudah ada
     */
    public static function assignKlinik(string $idPermohonanUjiKlinik): ?int
    {
        $permohonan = PermohonanUjiKlinik2::find($idPermohonanUjiKlinik);

        if (!$permohonan) {
            return null;
        }

        // Sudah ada nomer_lab — jangan timpa, kecuali manual & tidak cocok dengan nomor_lab_manual
        $existing = (int) ($permohonan->nomer_lab ?? 0);
        $isLabManual = (int) ($permohonan->is_nomor_lab_manual ?? 0) === 1;
        $manualLab = (int) preg_replace('/\D+/', '', (string) ($permohonan->nomor_lab_manual ?? ''));

        if ($isLabManual && $manualLab > 0) {
            if ($existing === $manualLab) {
                return null;
            }
            $permohonan->nomer_lab = $manualLab;
            $permohonan->save();
            self::raiseLastNumberToAtLeast($manualLab, (int) date('Y', strtotime((string) ($permohonan->created_at ?? 'now'))));

            return $manualLab;
        }

        if ($existing > 0) {
            return null;
        }

        $nomerLab = self::getNextNumber();
        $permohonan->nomer_lab = $nomerLab;
        $permohonan->save();

        return $nomerLab;
    }

    /**
     * Assign nomer_lab ke PermohonanUji kesmas (legacy: satu nomor untuk semua lab).
     * Tidak digunakan lagi – digantikan oleh assignKesmasPerLabIfAllDone.
     *
     * @deprecated Gunakan assignKesmasPerLabIfAllDone
     */
    public static function assignKesmasIfAllDone(string $idPermohonanUji): ?int
    {
        return null; // Tidak digunakan lagi
    }

    /**
     * Assign nomer_lab per (lab + jenis sampel) untuk kesmas.
     *
     * Logika:
     *  - Untuk lab ($labId) dan jenis sampel ($sampleTypeId) yang diberikan, cari semua
     *    sample dalam PermohonanUji dengan jenis sampel tersebut yang mempunyai metode
     *    di lab tersebut (via tb_sample_method).
     *  - Jika SEMUA sample tersebut sudah memiliki PengesahanHasil untuk lab ini,
     *    maka terbitkan nomer_lab baru dan simpan ke tb_nomer_lab_kesmas.
     *  - Setiap kombinasi (lab + jenis sampel) mendapat nomer_lab sendiri, independen.
     *
     * @param  string       $idPermohonanUji   UUID PermohonanUji
     * @param  string       $labId             UUID Laboratorium (KIM / MBI / dll)
     * @param  string|null  $sampleTypeId      UUID jenis sampel (ms_sample_type)
     * @return int|null  Nomer lab yang ditetapkan, atau null jika belum selesai / sudah ada
     */
    public static function assignKesmasPerLabIfAllDone(string $idPermohonanUji, string $labId, ?string $sampleTypeId = null): ?int
    {
        // Sudah ada nomor untuk kombinasi ini?
        $existingQuery = NomerLabKesmas::where('permohonan_uji_id', $idPermohonanUji)
            ->where('laboratorium_id', $labId);
        if ($sampleTypeId) {
            $existingQuery->where('sample_type_id', $sampleTypeId);
        } else {
            $existingQuery->whereNull('sample_type_id');
        }
        if ($existingQuery->exists()) {
            return null; // Sudah punya nomor
        }

        // Ambil semua sample dalam permohonan ini yang mempunyai metode di lab ini
        // dengan jenis sampel yang sama.
        $sampleQuery = \DB::table('tb_samples as s')
            ->join('tb_sample_method as sm', function ($j) use ($labId) {
                $j->on('sm.sample_id', '=', 's.id_samples')
                  ->where('sm.laboratorium_id', $labId)
                  ->whereNull('sm.deleted_at');
            })
            ->where('s.permohonan_uji_id', $idPermohonanUji)
            ->whereNull('s.deleted_at');

        if ($sampleTypeId) {
            $sampleQuery->where('s.typesample_samples', $sampleTypeId);
        }

        $sampleIds = $sampleQuery->pluck('s.id_samples')->unique()->values();

        if ($sampleIds->isEmpty()) {
            return null;
        }

        // Cek apakah SEMUA sample tersebut sudah ada PengesahanHasil untuk lab ini
        $totalSamples = $sampleIds->count();
        $doneSamples  = \DB::table('tb_pengesahan_hasil')
            ->whereIn('sample_id', $sampleIds)
            ->where('laboratorium_id', $labId)
            ->whereNull('deleted_at')
            ->count();

        if ($doneSamples < $totalSamples) {
            return null; // Belum semua selesai
        }

        // Terbitkan nomer_lab baru
        $year     = (int) date('Y');
        $nomerLab = self::getNextNumber($year);

        NomerLabKesmas::create([
            'id'                => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'permohonan_uji_id' => $idPermohonanUji,
            'laboratorium_id'   => $labId,
            'sample_type_id'    => $sampleTypeId,
            'nomer_lab'         => $nomerLab,
            'year'              => $year,
        ]);

        return $nomerLab;
    }
}
