<?php

namespace Smt\Masterweb\Models;

use Smt\Masterweb\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\GlobalLabSequenceDetail;
use Smt\Masterweb\Helpers\IssuedNumber;

class GlobalLabSequence extends Model
{
    use Uuid;
    use SoftDeletes;

    public $incrementing = false;
    protected $table = "global_lab_sequence";
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'year',
        'last_number',
    ];

    /**
     * Tahun sekuens: selalu integer 4 digit. Default tahun kalender sekarang.
     * Setiap tahun punya baris sendiri → nomor otomatis kembali ke 1.
     */
    public static function resolveYear($year = null): int
    {
        if ($year === null || $year === '') {
            return (int) date('Y');
        }

        if ($year instanceof \DateTimeInterface) {
            return (int) $year->format('Y');
        }

        if (is_string($year) && preg_match('/^\d{4}-\d{2}-\d{2}/', $year)) {
            return (int) substr($year, 0, 4);
        }

        $parsed = (int) $year;

        return $parsed > 1900 ? $parsed : (int) date('Y');
    }

    /**
     * Get or create sequence for given year and return next number.
     * Alokasi atomic di koneksi terpisah (commit segera) + retry bila bentrok unik.
     *
     * @param int|string|null $year Year for the sequence
     * @param string|null $lab_id Lab ID that uses this number
     * @param string|null $lab_type Type: 'lab' for LabNum, 'klinik' for NumberKlinik
     * @param string|null $reference_id Reference ID from LabNum, NumberKlinik, or PermohonanUjiKlinik
     * @return int Next sequence number
     */
    public static function getNextNumber($year = null, $lab_id = null, $lab_type = null, $reference_id = null)
    {
        $year = self::resolveYear($year);
        $conn = \Smt\Masterweb\Helpers\SequenceDb::connectionName();

        // Scan data hidup DI LUAR lock agar tidak menahan baris global_lab_sequence lama.
        $maxLive = self::resolveMaxIssuedNumberFromLiveData($year);

        $attempts = 0;
        while ($attempts < 15) {
            $attempts++;
            try {
                return DB::connection($conn)->transaction(function () use ($conn, $year, $lab_id, $lab_type, $reference_id, $maxLive) {
                    $sequence = self::on($conn)->where('year', $year)->lockForUpdate()->first();
                    if (!$sequence) {
                        self::on($conn)->create([
                            'year' => $year,
                            'last_number' => 0,
                        ]);
                        $sequence = self::on($conn)->where('year', $year)->lockForUpdate()->first();
                    }

                    $maxBooked = (int) GlobalLabSequenceDetail::on($conn)
                        ->where('year', $year)
                        ->whereNull('deleted_at')
                        ->max('sequence_number');
                    $lastStored = (int) $sequence->last_number;

                    $base = max($maxLive, $maxBooked, $lastStored);
                    $next_number = $base + 1;

                    // Cek bentrok hanya pada booking detail (koneksi seq) — cepat di dalam lock.
                    $guard = 0;
                    while ($guard < 10000) {
                        $exists = GlobalLabSequenceDetail::on($conn)
                            ->where('year', $year)
                            ->where('sequence_number', $next_number)
                            ->whereNull('deleted_at')
                            ->exists();
                        if (!$exists) {
                            break;
                        }
                        $next_number++;
                        $guard++;
                    }

                    $sequence->last_number = $next_number;
                    $sequence->save();

                    GlobalLabSequenceDetail::on($conn)->create([
                        'year' => $year,
                        'sequence_number' => $next_number,
                        'lab_id' => $lab_id,
                        'lab_type' => $lab_type,
                        'reference_id' => $reference_id,
                    ]);

                    return $next_number;
                });
            } catch (\Throwable $e) {
                if (!\Smt\Masterweb\Helpers\SequenceDb::isDuplicateKeyException($e) || $attempts >= 15) {
                    throw $e;
                }
                // Refresh maxLive bila race, lalu retry
                $maxLive = self::resolveMaxIssuedNumberFromLiveData($year);
                usleep(50000 * $attempts);
            }
        }

        throw new \RuntimeException('Gagal alokasi nomor spesimen unik setelah beberapa percobaan.');
    }

    /**
     * Cek apakah nomor spesimen/sampel sudah terpakai (data hidup atau booking detail).
     */
    public static function isSpesimenNumberTaken(int $number, $year = null, ?string $excludeKlinikId = null): bool
    {
        if ($number < 1) {
            return false;
        }

        $year = self::resolveYear($year);
        $asString = (string) $number;

        try {
            if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
                $q = DB::table('tb_permohonan_uji_klinik_2')
                    ->whereNull('deleted_at')
                    ->where(function ($q) use ($year) {
                        $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                            ->orWhere(function ($q2) use ($year) {
                                $q2->whereNull('tglregister_permohonan_uji_klinik')
                                    ->whereYear('created_at', $year);
                            });
                    })
                    ->where(function ($q) use ($number, $asString) {
                        // Manual aktif: pakai nomor_spesimen_manual / noregister (abaikan nourut stale)
                        $q->where(function ($q2) use ($number, $asString) {
                            $q2->where('is_nomor_spesimen_manual', 1)
                                ->where(function ($q3) use ($number, $asString) {
                                    $q3->where('nomor_spesimen_manual', $asString)
                                        ->orWhereRaw('CAST(TRIM(nomor_spesimen_manual) AS UNSIGNED) = ?', [$number])
                                        ->orWhere('noregister_permohonan_uji_klinik', $asString)
                                        ->orWhere('noregister_permohonan_uji_klinik', 'like', $asString . ' /%');
                                });
                        })
                        // Otomatis: nourut + noregister
                        ->orWhere(function ($q2) use ($number, $asString) {
                            $q2->where(function ($q3) {
                                $q3->where('is_nomor_spesimen_manual', 0)
                                    ->orWhereNull('is_nomor_spesimen_manual');
                            })
                            ->where(function ($q3) use ($number, $asString) {
                                $q3->where('nourut_permohonan_uji_klinik', $number)
                                    ->orWhere('noregister_permohonan_uji_klinik', $asString)
                                    ->orWhere('noregister_permohonan_uji_klinik', 'like', $asString . '/%')
                                    ->orWhere('noregister_permohonan_uji_klinik', 'like', $asString . ' /%');
                            });
                        });
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

        try {
            if (Schema::hasTable('tb_samples')) {
                $sampleTaken = DB::table('tb_samples')
                    ->whereNull('deleted_at')
                    ->whereYear('created_at', $year)
                    ->where(function ($q) use ($number, $asString) {
                        $q->where('count_id', $number)
                            ->orWhere('codesample_samples', 'like', '%/' . $asString . '/%');
                    })
                    ->exists();
                if ($sampleTaken) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            if (Schema::hasTable('global_lab_sequence_detail')) {
                $dq = GlobalLabSequenceDetail::where('year', $year)
                    ->where('sequence_number', $number)
                    ->whereNull('deleted_at')
                    // Booking orphan (reference_id kosong) tidak menghalangi — hanya yang terhubung record
                    ->whereNotNull('reference_id')
                    ->where('reference_id', '!=', '');

                if ($excludeKlinikId) {
                    $dq->where('reference_id', '!=', $excludeKlinikId);
                }

                return $dq->exists();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return false;
    }

    /**
     * Ambil nomor spesimen unik (untuk input manual yang bentrok).
     * Atomic di koneksi terpisah + naikkan counter.
     */
    public static function resolveUniqueSpesimenNumber(
        $preferred = null,
        $year = null,
        ?string $excludeKlinikId = null,
        ?int $minExclusive = null
    ): int {
        $year = self::resolveYear($year);
        $conn = \Smt\Masterweb\Helpers\SequenceDb::connectionName();

        // Preferensi + cek data hidup DI LUAR lock.
        $preferred = (int) preg_replace('/\D+/', '', (string) ($preferred ?? ''));
        $maxLive = self::resolveMaxIssuedNumberFromLiveData($year);

        $candidate = $preferred > 0 ? $preferred : ($maxLive + 1);
        if ($minExclusive !== null) {
            $candidate = max($candidate, ((int) $minExclusive) + 1);
        }

        $guard = 0;
        while ($guard < 10000 && self::isSpesimenNumberTaken($candidate, $year, $excludeKlinikId)) {
            $candidate++;
            $guard++;
        }
        $candidate = max(1, $candidate);

        // Update counter singkat di koneksi seq (commit segera).
        DB::connection($conn)->transaction(function () use ($conn, $year, $candidate) {
            $sequence = self::on($conn)->where('year', $year)->lockForUpdate()->first();
            if (!$sequence) {
                self::on($conn)->create([
                    'year' => $year,
                    'last_number' => $candidate,
                ]);

                return;
            }

            if ((int) $sequence->last_number < $candidate) {
                $sequence->last_number = $candidate;
                $sequence->save();
            }
        });

        return $candidate;
    }

    /**
     * Get current number without incrementing (per tahun).
     * Selalu dihitung ulang dari max nomor sampel/lab yang masih hidup.
     */
    public static function getCurrentNumber($year = null)
    {
        $year = self::resolveYear($year);

        // Baca cepat tanpa lock/sync — sync penuh ditangani getNextNumber.
        $stored = (int) (self::where('year', $year)->value('last_number') ?? 0);
        $maxLive = self::resolveMaxIssuedNumberFromLiveData($year);

        return max($stored, $maxLive);
    }

    /**
     * Naikkan last_number tahun terkait agar minimal = $minNumber (input nomor lab manual).
     * Tidak mempengaruhi tahun lain.
     */
    public static function raiseLastNumberToAtLeast(int $minNumber, $year = null): void
    {
        if ($minNumber < 1) {
            return;
        }

        $year = self::resolveYear($year);

        if ($minNumber > 999999) {
            return;
        }

        // Scan di luar lock; update counter di koneksi seq (commit segera).
        $maxLive = self::resolveMaxIssuedNumberFromLiveData($year);
        if ($maxLive > 0 && $minNumber > ($maxLive + 100)) {
            $target = $maxLive;
        } else {
            $target = max($maxLive, $minNumber);
        }

        $conn = \Smt\Masterweb\Helpers\SequenceDb::connectionName();
        DB::connection($conn)->transaction(function () use ($conn, $year, $target) {
            $sequence = self::on($conn)->where('year', $year)->lockForUpdate()->first();
            if (!$sequence) {
                self::on($conn)->create([
                    'year' => $year,
                    'last_number' => $target,
                ]);

                return;
            }

            // Jangan pernah turunkan last_number di sini (cegah reuse nomor)
            if ((int) $sequence->last_number < $target) {
                $sequence->last_number = $target;
                $sequence->save();
            }
        });
    }

    /**
     * Max nomor yang benar-benar terpakai di data hidup (bukan global_lab_sequence_detail).
     *
     * Sumber nomor spesimen/sampel (bukan nomor lab):
     * - Kode sampel kesmas (bagian tengah AM.01/####/YYYY)
     * - nomor_spesimen_manual / noregister klinik yang tampil
     *
     * Tidak memakai nourut klinik, tb_lab_num, atau nomer_lab (bisa junk / beda urutan).
     */
    public static function resolveMaxIssuedNumberFromLiveData($year = null): int
    {
        $year = self::resolveYear($year);
        $nums = [];

        try {
            if (Schema::hasTable('tb_samples')) {
                $codes = DB::table('tb_samples')
                    ->whereNull('deleted_at')
                    ->whereYear('created_at', $year)
                    ->whereNotNull('codesample_samples')
                    ->where('codesample_samples', 'like', '%/%/%')
                    ->pluck('codesample_samples');
                foreach ($codes as $code) {
                    if (preg_match('#/(\d{1,6})/\d{4}\s*$#', (string) $code, $m)) {
                        $nums[] = (int) $m[1];
                    }
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

                $cols = [
                    'nomor_spesimen_manual',
                    'noregister_permohonan_uji_klinik',
                    'nourut_permohonan_uji_klinik',
                ];

                $rows = DB::table('tb_permohonan_uji_klinik_2')
                    ->whereNull('deleted_at')
                    ->where($klinikYear)
                    ->get($cols);

                foreach ($rows as $row) {
                    $manual = preg_replace('/\D+/', '', trim((string) ($row->nomor_spesimen_manual ?? '')));
                    if ($manual !== '' && strlen($manual) <= 6) {
                        $nums[] = (int) $manual;
                        continue;
                    }

                    $noreg = trim((string) ($row->noregister_permohonan_uji_klinik ?? ''));
                    if ($noreg !== '') {
                        if (preg_match('/^(\d{1,6})$/', $noreg, $m)) {
                            $nums[] = (int) $m[1];
                            continue;
                        }
                        if (preg_match('/^(\d{1,6})\s*\//', $noreg, $m)) {
                            $nums[] = (int) $m[1];
                            continue;
                        }
                        if (preg_match('#(?:^|/)\s*(\d{1,6})\s*/\s*\d{4}\s*$#', $noreg, $m)) {
                            $nums[] = (int) $m[1];
                            continue;
                        }
                    }

                    // Fallback: nourut (penting untuk batch import sebelum noregister ter-format)
                    $nourut = preg_replace('/\D+/', '', trim((string) ($row->nourut_permohonan_uji_klinik ?? '')));
                    if ($nourut !== '' && strlen($nourut) <= 6 && (int) $nourut > 0) {
                        $nums[] = (int) $nourut;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return IssuedNumber::maxDense($nums);
    }

    /**
     * Samakan last_number HANYA untuk $year dari max nomor sampel/lab yang masih hidup.
     * TIDAK memakai global_lab_sequence_detail sebagai acuan (bisa stale setelah hapus).
     *
     * Tahun tanpa data → target 0 → getNextNumber menghasilkan 1.
     * last_number diset ke target (bisa naik atau turun).
     *
     * @param  int|string|null  $year
     * @param  bool  $alreadyInTransaction
     */
    public static function ensureSyncedWithManualSources($year = null, bool $alreadyInTransaction = false): void
    {
        $year = self::resolveYear($year);

        // Scan di luar lock agar tidak menahan baris sequence selama query berat.
        $target = self::resolveMaxIssuedNumberFromLiveData($year);

        $runner = function () use ($year, $target, $alreadyInTransaction) {
            // Pakai koneksi seq kecuali caller sudah memegang transaksi default (resequence).
            if ($alreadyInTransaction) {
                $sequence = self::where('year', $year)->lockForUpdate()->first();
                if (!$sequence) {
                    self::create([
                        'year' => $year,
                        'last_number' => $target,
                    ]);

                    return;
                }
                // Di dalam resequence: boleh set tepat ke target (termasuk turun setelah hapus).
                if ((int) $sequence->last_number !== $target) {
                    $sequence->last_number = $target;
                    $sequence->save();
                }

                return;
            }

            $conn = \Smt\Masterweb\Helpers\SequenceDb::connectionName();
            DB::connection($conn)->transaction(function () use ($conn, $year, $target) {
                $sequence = self::on($conn)->where('year', $year)->lockForUpdate()->first();
                if (!$sequence) {
                    self::on($conn)->create([
                        'year' => $year,
                        'last_number' => $target,
                    ]);

                    return;
                }

                // Jangan turunkan via sync biasa di luar resequence (cegah reuse nomor).
                if ((int) $sequence->last_number < $target) {
                    $sequence->last_number = $target;
                    $sequence->save();
                }
            });
        };

        $runner();
    }

    /**
     * Unlink mapping for a deleted LabNum record
     */
    public static function unlinkByLabNumId(string $labNumId): void
    {
        DB::table('global_lab_sequence_detail')
            ->where('lab_type', 'lab')
            ->where('reference_id', $labNumId)
            ->whereNull('deleted_at')
            ->update(['reference_id' => null, 'updated_at' => now()]);
    }

    /** Soft-delete mapping row(s) for a LabNum, lalu sync max nomor hidup */
    public static function deleteByLabNumId(string $labNumId): void
    {
        $years = DB::table('global_lab_sequence_detail')
            ->where('lab_type', 'lab')
            ->where('reference_id', $labNumId)
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('year')
            ->map(function ($y) {
                return (int) $y;
            })
            ->all();

        DB::table('global_lab_sequence_detail')
            ->where('lab_type', 'lab')
            ->where('reference_id', $labNumId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        if (empty($years)) {
            $years = [(int) date('Y')];
        }

        foreach ($years as $year) {
            self::ensureSyncedWithManualSources($year);
        }
    }

    /**
     * Unlink mapping for a deleted PermohonanUjiKlinik2 record
     */
    public static function unlinkByKlinikId(string $pukId): void
    {
        DB::table('global_lab_sequence_detail')
            ->where('lab_type', 'klinik')
            ->where('reference_id', $pukId)
            ->whereNull('deleted_at')
            ->update(['reference_id' => null, 'updated_at' => now()]);
    }

    /**
     * Soft-delete mapping row(s) for a PermohonanUjiKlinik2.
     * Sync counter sebaiknya dipanggil SETELAH permohonan soft-delete (lihat $sync).
     *
     * @param  bool  $sync  true = sync segera; false = caller sync setelah hapus data hidup
     * @return int[] tahun yang terdampak
     */
    public static function deleteByKlinikId(string $pukId, bool $sync = true): array
    {
        $years = DB::table('global_lab_sequence_detail')
            ->where('lab_type', 'klinik')
            ->where('reference_id', $pukId)
            ->whereNull('deleted_at')
            ->distinct()
            ->pluck('year')
            ->map(function ($y) {
                return (int) $y;
            })
            ->all();

        DB::table('global_lab_sequence_detail')
            ->where('lab_type', 'klinik')
            ->where('reference_id', $pukId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        if (empty($years)) {
            $years = [(int) date('Y')];
        }

        if ($sync) {
            foreach ($years as $year) {
                self::ensureSyncedWithManualSources($year);
            }
        }

        return array_values(array_unique(array_map('intval', $years)));
    }

    /**
     * Setelah hapus/edit sampel: sync ulang counter tahun dari max nomor hidup.
     */
    public static function syncAfterSampleChange($year = null): void
    {
        self::ensureSyncedWithManualSources($year);
    }

    /**
     * Relink mappings (best-effort) for the given year using current data
     * - lab: match by lab_number = sequence_number
     * - klinik: match by nourut_permohonan_uji_klinik = sequence_number
     */
    public static function relinkMappingsForYear(int $year): void
    {
        // Relink for non-klinik lab numbers
        DB::statement("UPDATE global_lab_sequence_detail g
            JOIN tb_lab_num ln ON ln.id_lab_num = (
                SELECT ln2.id_lab_num FROM tb_lab_num ln2
                WHERE ln2.lab_number = g.sequence_number
                  AND YEAR(ln2.created_at) = g.year
                  AND ln2.deleted_at IS NULL
                ORDER BY ln2.created_at DESC
                LIMIT 1
            )
            SET g.reference_id = ln.id_lab_num, g.updated_at = NOW()
            WHERE g.lab_type = 'lab' AND g.year = ?", [$year]);

        // Relink for klinik registrations
        DB::statement("UPDATE global_lab_sequence_detail g
            JOIN tb_permohonan_uji_klinik_2 p ON p.nourut_permohonan_uji_klinik = g.sequence_number
                AND YEAR(p.created_at) = g.year
                AND p.deleted_at IS NULL
            SET g.reference_id = p.id_permohonan_uji_klinik, g.updated_at = NOW()
            WHERE g.lab_type = 'klinik' AND g.year = ?", [$year]);
    }

    /**
     * Sync global_lab_sequence.last_number to match the largest known number for the year
     * (detail + nomor lab manual klinik + lab_number kesmas).
     * @param int|null $year Year to sync (defaults to current year)
     */
    public static function syncLastNumberFromDetail(int $year = null): void
    {
        self::ensureSyncedWithManualSources($year);
    }

    /**
     * Apakah detail sequence terkait record nomor MANUAL SPESIMEN/SAMPLE.
     * GlobalLabSequence = urutan spesimen; nomor lab punya NomerLabSequence sendiri.
     * Lab manual saja TIDAK membuat spesimen dianggap manual (tetap ikut resequence saat hapus).
     */
    public static function isManualLinkedDetail(string $labType, $referenceId): bool
    {
        if (empty($referenceId)) {
            return false;
        }

        if ($labType === 'klinik') {
            $row = DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', $referenceId)
                ->whereNull('deleted_at')
                ->first(['is_nomor_spesimen_manual', 'nomor_spesimen_manual']);

            if (!$row) {
                return false;
            }

            return (int) ($row->is_nomor_spesimen_manual ?? 0) === 1
                && trim((string) ($row->nomor_spesimen_manual ?? '')) !== '';
        }

        if ($labType === 'lab') {
            $row = DB::table('tb_lab_num as ln')
                ->leftJoin('tb_samples as s', function ($join) {
                    $join->on('s.id_samples', '=', 'ln.sample_id')
                        ->whereNull('s.deleted_at');
                })
                ->where('ln.id_lab_num', $referenceId)
                ->whereNull('ln.deleted_at')
                ->first([
                    's.is_nomor_sampel_manual',
                    's.is_nomor_laboratorium_manual',
                ]);

            if (!$row) {
                return false;
            }

            return (int) ($row->is_nomor_sampel_manual ?? 0) === 1
                || (int) ($row->is_nomor_laboratorium_manual ?? 0) === 1;
        }

        return false;
    }

    /**
     * Auto-sort nomor untuk tahun tertentu. Hanya untuk tombol/aksi sort eksplisit.
     * Jangan panggil saat hapus — itu menimpa nourut/noregister sisa record.
     *
     * - Record nomor MANUAL (klinik/kesmas) tidak diubah
     * - Record otomatis diurut ulang, melewati angka yang dipakai nomor manual
     * - Propagasi ke tb_lab_num / tb_samples / tb_permohonan_uji_klinik_2 (hanya non-manual)
     *
     * @return array{manual:int, auto:int, year:int}
     */
    public static function resequenceAutoOnlyForYear($year = null): array
    {
        $year = self::resolveYear($year);

        return DB::transaction(function () use ($year) {
            $details = DB::table('global_lab_sequence_detail')
                ->where('year', $year)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->orderBy('sequence_number', 'asc')
                ->lockForUpdate()
                ->get(['id', 'sequence_number', 'lab_type', 'reference_id', 'created_at']);

            $manualIds = [];
            $manualNumbers = [];
            $autoDetails = [];

            foreach ($details as $detail) {
                $isManual = self::isManualLinkedDetail(
                    (string) ($detail->lab_type ?? ''),
                    $detail->reference_id
                );

                if ($isManual) {
                    $manualIds[$detail->id] = true;
                    $num = (int) $detail->sequence_number;
                    if ($num > 0) {
                        $manualNumbers[$num] = true;
                    }
                } else {
                    $autoDetails[] = $detail;
                }
            }

            // Assign nomor baru hanya untuk auto, skip angka yang dipakai manual
            $next = 1;
            $updates = []; // id => new_seq
            foreach ($autoDetails as $detail) {
                while (isset($manualNumbers[$next])) {
                    $next++;
                }
                $updates[$detail->id] = $next;
                $next++;
            }

            foreach ($updates as $detailId => $newSeq) {
                DB::table('global_lab_sequence_detail')
                    ->where('id', $detailId)
                    ->update([
                        'sequence_number' => $newSeq,
                        'updated_at' => now(),
                    ]);
            }

            $oldKlinikNumbers = [];
            $klinikIdsBefore = [];
            foreach ($autoDetails as $detail) {
                if ((string) ($detail->lab_type ?? '') === 'klinik' && !empty($detail->reference_id)) {
                    $klinikIdsBefore[] = $detail->reference_id;
                }
            }
            if (!empty($klinikIdsBefore)) {
                $oldKlinikNumbers = DB::table('tb_permohonan_uji_klinik_2')
                    ->whereIn('id_permohonan_uji_klinik', $klinikIdsBefore)
                    ->get([
                        'id_permohonan_uji_klinik',
                        'nourut_permohonan_uji_klinik',
                        'noregister_permohonan_uji_klinik',
                    ])
                    ->keyBy('id_permohonan_uji_klinik')
                    ->all();
            }

            // Propagasi ke Kesmas (skip manual sampel/lab)
            if (!empty($updates) || !empty($manualIds)) {
                DB::statement("
                    UPDATE tb_lab_num ln
                    JOIN tb_samples s ON s.id_samples = ln.sample_id AND s.deleted_at IS NULL
                    JOIN global_lab_sequence_detail g
                      ON BINARY g.reference_id = BINARY ln.id_lab_num
                     AND BINARY g.lab_type = 'lab'
                     AND g.year = ?
                     AND g.deleted_at IS NULL
                    SET ln.lab_number = g.sequence_number
                    WHERE ln.deleted_at IS NULL
                      AND YEAR(ln.created_at) = ?
                      AND (s.is_nomor_sampel_manual = 0 OR s.is_nomor_sampel_manual IS NULL)
                      AND (s.is_nomor_laboratorium_manual = 0 OR s.is_nomor_laboratorium_manual IS NULL)
                ", [$year, $year]);

                DB::statement("
                    UPDATE tb_samples s
                    JOIN tb_lab_num ln ON ln.sample_id = s.id_samples AND ln.deleted_at IS NULL
                    JOIN global_lab_sequence_detail g
                      ON BINARY g.reference_id = BINARY ln.id_lab_num
                     AND BINARY g.lab_type = 'lab'
                     AND g.year = ?
                     AND g.deleted_at IS NULL
                    SET
                      s.codesample_samples = CONCAT(
                          SUBSTRING_INDEX(s.codesample_samples, '/', 1),
                          '/',
                          LPAD(g.sequence_number, 4, '0'),
                          '/',
                          SUBSTRING_INDEX(s.codesample_samples, '/', -1)
                      ),
                      s.count_id = LPAD(g.sequence_number, 4, '0')
                    WHERE s.deleted_at IS NULL
                      AND YEAR(s.created_at) = ?
                      AND (s.is_nomor_sampel_manual = 0 OR s.is_nomor_sampel_manual IS NULL)
                      AND (s.is_nomor_laboratorium_manual = 0 OR s.is_nomor_laboratorium_manual IS NULL)
                      AND s.codesample_samples IS NOT NULL
                      AND s.codesample_samples LIKE '%/%/%'
                ", [$year, $year]);

                // Propagasi ke Klinik: hanya skip spesimen manual.
                // Lab manual tetap boleh di-resequence spesimennya (nourut/noregister),
                // nomor_lab_manual tidak diubah di sini.
                DB::statement("
                    UPDATE tb_number_klinik n
                    JOIN global_lab_sequence_detail g
                      ON BINARY g.reference_id = BINARY n.id_permohonan_uji_klinik
                     AND BINARY g.lab_type = 'klinik'
                     AND g.year = ?
                     AND g.deleted_at IS NULL
                    JOIN tb_permohonan_uji_klinik_2 p
                      ON p.id_permohonan_uji_klinik = n.id_permohonan_uji_klinik
                     AND p.deleted_at IS NULL
                    SET
                      n.new_number = LPAD(g.sequence_number, 4, '0'),
                      n.last_number = LPAD(g.sequence_number, 4, '0')
                    WHERE n.deleted_at IS NULL
                      AND YEAR(n.created_at) = ?
                      AND (p.is_nomor_spesimen_manual = 0 OR p.is_nomor_spesimen_manual IS NULL)
                ", [$year, $year]);

                DB::statement("
                    UPDATE tb_permohonan_uji_klinik_2 p
                    JOIN global_lab_sequence_detail g
                      ON BINARY g.reference_id = BINARY p.id_permohonan_uji_klinik
                     AND BINARY g.lab_type = 'klinik'
                     AND g.year = ?
                     AND g.deleted_at IS NULL
                    JOIN (
                        SELECT
                          id_permohonan_uji_klinik,
                          ROW_NUMBER() OVER (ORDER BY created_at ASC) AS registration_count
                        FROM tb_permohonan_uji_klinik_2
                        WHERE YEAR(created_at) = ?
                          AND deleted_at IS NULL
                          AND (is_nomor_spesimen_manual = 0 OR is_nomor_spesimen_manual IS NULL)
                    ) reg ON reg.id_permohonan_uji_klinik = p.id_permohonan_uji_klinik
                    SET
                      p.nourut_permohonan_uji_klinik = g.sequence_number,
                      p.noregister_permohonan_uji_klinik = CONCAT(
                        LPAD(g.sequence_number, 4, '0'),
                        '/',
                        LPAD(reg.registration_count, 4, '0')
                      )
                    WHERE p.deleted_at IS NULL
                      AND YEAR(p.created_at) = ?
                      AND (p.is_nomor_spesimen_manual = 0 OR p.is_nomor_spesimen_manual IS NULL)
                ", [$year, $year, $year]);
            }

            self::ensureSyncedWithManualSources($year, true);

            self::logResequenceNumberChanges($year, $oldKlinikNumbers);

            return [
                'year' => $year,
                'manual' => count($manualIds),
                'auto' => count($updates),
            ];
        });
    }

    /**
     * Catat jejak jika sort eksplisit mengubah nomor. Tidak mengubah nomor.
     *
     * @param  array<string,object>  $oldKlinikNumbers
     */
    private static function logResequenceNumberChanges(int $year, array $oldKlinikNumbers): void
    {
        if (empty($oldKlinikNumbers)) {
            return;
        }

        try {
            $ids = array_keys($oldKlinikNumbers);
            $newRows = DB::table('tb_permohonan_uji_klinik_2')
                ->whereIn('id_permohonan_uji_klinik', $ids)
                ->get([
                    'id_permohonan_uji_klinik',
                    'nourut_permohonan_uji_klinik',
                    'noregister_permohonan_uji_klinik',
                ]);

            $fields = [
                'nourut_permohonan_uji_klinik',
                'noregister_permohonan_uji_klinik',
            ];

            foreach ($newRows as $row) {
                $id = (string) $row->id_permohonan_uji_klinik;
                $oldRow = $oldKlinikNumbers[$id] ?? null;
                if ($oldRow === null) {
                    continue;
                }

                foreach ($fields as $field) {
                    $old = (string) ($oldRow->{$field} ?? '');
                    $new = (string) ($row->{$field} ?? '');
                    if ($old === $new) {
                        continue;
                    }

                    \Smt\Masterweb\Helpers\NomorChangeLogger::record([
                        'subject_type' => 'klinik',
                        'subject_id' => $id,
                        'field_name' => $field,
                        'old_value' => $old,
                        'new_value' => $new,
                        'event' => 'penggantian',
                        'source' => 'resequence',
                        'note' => 'Sort nomor otomatis tahun ' . $year,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // history tidak boleh menggagalkan sort
        }
    }
}