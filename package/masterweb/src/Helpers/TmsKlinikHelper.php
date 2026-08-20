<?php

namespace Smt\Masterweb\Helpers;

/**
 * Mapping parameter alat TMS (parameter_id) ke nama parameter satuan klinik.
 */
class TmsKlinikHelper
{
    /**
     * Sementara: Plasma NaF diperlakukan digit barcode sama dengan Plasma (3).
     * Dipakai mqtt:replay-not-applied saja; jangan aktifkan di subscriber harian.
     *
     * @var bool
     */
    protected static $plasmaSampleDigitAlias = false;

    /**
     * @param  bool  $enabled
     * @return void
     */
    public static function setPlasmaSampleDigitAlias($enabled)
    {
        self::$plasmaSampleDigitAlias = (bool) $enabled;
    }

    /**
     * @return bool
     */
    public static function plasmaSampleDigitAlias(): bool
    {
        return self::$plasmaSampleDigitAlias;
    }

    /**
     * Digit jenis plasma setara (3 = Plasma, 8 = Plasma NaF) bila alias aktif.
     *
     * @param  string  $digitA
     * @param  string  $digitB
     * @return bool
     */
    public static function plasmaTypeDigitsEquivalent(string $digitA, string $digitB): bool
    {
        if ($digitA === $digitB) {
            return true;
        }
        if (!self::$plasmaSampleDigitAlias) {
            return false;
        }

        return in_array($digitA, ['3', '8'], true) && in_array($digitB, ['3', '8'], true);
    }

    /**
     * Acuan parameter TMS.
     * key = parameter_id di biolis_results (tabel hasil alat TMS)
     *
     * @return array<int, array{name: string, match: string[]}>
     */
    public static function acuanParameters(): array
    {
        return [
            2 => [
                'name' => 'Glukosa',
                'match' => [
                    'glukosa darah puasa', 'gula darah puasa',
                    'gula darah 2 jam pp', 'gd 2 jam pp', 'gula darah 2jpp', 'gd 2jpp', '2 jam pp',
                    'glukosa darah sewaktu', 'gula darah sewaktu',
                    'glukosa darah', 'gula darah', 'glukosa', 'gdn', 'gds', 'gpp',
                ],
            ],
            3 => [
                'name' => 'Ureum',
                'match' => ['ureum', 'urea'],
            ],
            4 => [
                'name' => 'CreatPAP',
                'match' => ['creatinine', 'creatinin', 'creat'],
            ],
            6 => [
                'name' => 'SGPT',
                'match' => ['sgpt', 'alt'],
            ],
            7 => [
                'name' => 'SGOT',
                'match' => ['sgot', 'ast'],
            ],
            8 => [
                'name' => 'UA',
                'match' => ['asam urat', 'uric acid', 'ua'],
            ],
            9 => [
                'name' => 'Bil T',
                'match' => ['bilirubin total', 'bil t', 'bilt'],
            ],
            10 => [
                'name' => 'Bil D',
                'match' => ['bilirubin direk', 'bilirubin direct', 'bil d', 'bild'],
            ],
            11 => [
                'name' => 'Alb',
                'match' => ['albumin'],
            ],
            12 => [
                'name' => 'T Prot',
                'match' => ['total protein', 't prot', 'tprot'],
            ],
            13 => [
                'name' => 'ALP',
                'match' => ['alkaline phosphatase', 'alp'],
            ],
            14 => [
                'name' => 'GGT',
                'match' => ['gamma gt', 'gamma-gt', 'ggt'],
            ],
            15 => [
                'name' => 'CholPro',
                'match' => ['cholesterol total', 'kolesterol total', 'cholpro', 'cholesterol', 'kolesterol', 'chol'],
            ],
            16 => [
                'name' => 'TrigPro',
                'match' => ['trigliserid', 'triglyceride', 'trigpro', 'trig'],
            ],
            17 => [
                'name' => 'CHE',
                'match' => ['cholinesterase', 'che'],
            ],
            18 => [
                'name' => 'HDL Sek',
                'match' => ['hdl cholesterol', 'hdl'],
            ],
            19 => [
                'name' => 'LDL Sek',
                'match' => ['ldl cholesterol', 'ldl'],
            ],
            20 => [
                'name' => 'Chol Sek',
                'match' => ['cholesterol total', 'kolesterol total', 'chol sek', 'cholesterol', 'kolesterol', 'chol'],
            ],
            21 => [
                'name' => 'Trig Sek',
                'match' => ['trigliserid', 'triglyceride', 'trig sek', 'trig'],
            ],
        ];
    }

    /**
     * Cocokkan nama parameter satuan klinik ke salah satu acuan TMS.
     *
     * @return array{parameter_id: int, name: string}|null
     */
    public static function matchSatuanName(string $satuanName): ?array
    {
        $name = self::normalize($satuanName);
        if ($name === '') {
            return null;
        }

        // Hindari false positive
        if (strpos($name, 'mikroalbumin') !== false || strpos($name, 'protein urin') !== false) {
            // Alb/T Prot tidak untuk urine
        }

        $best = null;
        $bestLen = 0;

        foreach (self::acuanParameters() as $id => $meta) {
            foreach ($meta['match'] as $key) {
                $key = self::normalize($key);
                if ($key === '') {
                    continue;
                }

                $exactOnly = strlen($key) <= 3;
                $matched = false;
                if ($exactOnly) {
                    $matched = ($name === $key) || preg_match('/\b' . preg_quote($key, '/') . '\b/', $name);
                } else {
                    $matched = ($name === $key) || (strpos($name, $key) !== false);
                }

                // Jangan map HDL/LDL ke Chol*
                if ($matched && in_array($id, [15, 20], true) && preg_match('/\b(hdl|ldl)\b/', $name)) {
                    $matched = false;
                }
                // Parameter urin jangan terikat ke channel kimia darah/serum
                if ($matched && strpos($name, 'urin') !== false) {
                    $matched = false;
                }
                // Albumin: jangan mikroalbumin
                if ($matched && $id === 11 && strpos($name, 'mikro') !== false) {
                    $matched = false;
                }
                // Protein: hanya total protein
                if ($matched && $id === 12 && strpos($name, 'total') === false && $name !== 't prot' && $name !== 'tprot') {
                    if ($name === 'protein') {
                        $matched = false;
                    }
                }

                if ($matched && strlen($key) > $bestLen) {
                    $bestLen = strlen($key);
                    $best = [
                        'parameter_id' => (int) $id,
                        'name' => $meta['name'],
                    ];
                }
            }
        }

        return $best;
    }

    /**
     * Resolve id_parameter_tms dari parameter permohonan (kolom langsung, master satuan, lalu nama).
     *
     * @param  mixed  $param
     * @return int
     */
    public static function resolveTmsIdFromParam($param): int
    {
        if (!$param) {
            return 0;
        }

        $tmsId = (int) ($param->id_parameter_tms ?? 0);
        if ($tmsId <= 0 && isset($param->parametersatuanklinik) && $param->parametersatuanklinik) {
            $tmsId = (int) ($param->parametersatuanklinik->id_parameter_tms ?? 0);
        }
        if ($tmsId <= 0) {
            $satuanName = (string) (
                optional($param->parametersatuanklinik)->name_parameter_satuan_klinik
                ?? ($param->nama_parameter_satuan_klinik ?? '')
            );
            $map = self::matchSatuanName($satuanName);
            if ($map) {
                $tmsId = (int) $map['parameter_id'];
            }
        }

        return $tmsId > 0 ? $tmsId : 0;
    }

    public static function formatResultValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Alat TMS sering mengisi padding '^' di akhir nilai (ASTM/HL7), e.g. 82^^^^^^^^^
        $value = trim((string) $value);
        $value = preg_replace('/[\^\x{FF3E}\x{02C6}]+$/u', '', $value) ?? $value;
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (!is_numeric($value)) {
            return $value;
        }

        $num = (float) $value;
        // Rapikan desimal panjang dari alat
        $formatted = rtrim(rtrim(number_format($num, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    public static function normalize(string $value): string
    {
        $value = strtolower(trim(preg_replace('/\s+/u', ' ', $value)));
        $value = str_replace(['_', '-'], ' ', $value);

        return $value;
    }

    /**
     * Angka urut nomor spesimen (tanpa padding).
     *
     * @param mixed $permohonan
     * @return int
     */
    public static function specimenUrutNumber($permohonan): int
    {
        if (!$permohonan) {
            return 0;
        }

        $urut = 0;
        if (method_exists($permohonan, 'resolveSpesimenUrut')) {
            $urut = (int) preg_replace('/\D+/', '', (string) $permohonan->resolveSpesimenUrut());
        }
        if ($urut < 1) {
            $urut = (int) preg_replace('/\D+/', '', (string) ($permohonan->nourut_permohonan_uji_klinik ?? ''));
        }
        if ($urut < 1 && !empty($permohonan->nomor_spesimen_manual)) {
            $urut = (int) preg_replace('/\D+/', '', (string) $permohonan->nomor_spesimen_manual);
        }

        return $urut > 0 ? $urut : 0;
    }

    /**
     * Tanggal lahir pasien sebagai DDMMYY (6 digit) untuk barcode.
     *
     * @param mixed $permohonan
     * @return string
     */
    public static function birthDateDmy($permohonan): string
    {
        if (!$permohonan) {
            return '';
        }

        $raw = null;
        $pasien = null;
        if (is_object($permohonan)) {
            if (isset($permohonan->pasien)) {
                $pasien = $permohonan->pasien;
            } elseif (method_exists($permohonan, 'pasien')) {
                try {
                    $pasien = $permohonan->pasien;
                } catch (\Throwable $e) {
                    $pasien = null;
                }
            }
        }

        if ($pasien) {
            $raw = $pasien->tgllahir_pasien ?? null;
        }
        if (empty($raw) && is_object($permohonan)) {
            $raw = $permohonan->tgllahir_pasien
                ?? $permohonan->tanggal_lahir
                ?? $permohonan->tgllahir_pasien_permohonan_uji_klinik
                ?? null;
        }

        if (empty($raw)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($raw)->format('dmy');
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Basis barcode 9 digit: {DDMM lahir}{nomor spesimen 5}.
     * Contoh lahir 26/02/1992 + spesimen 3902 → 260203902
     *
     * @param mixed $permohonan
     * @return string
     */
    public static function labelRegisterCode($permohonan): string
    {
        $urut = self::specimenUrutNumber($permohonan);
        if ($urut < 1) {
            return '';
        }

        $dob = self::birthDateDmy($permohonan);
        if ($dob === '') {
            $dob = self::registerDateDmy($permohonan);
        }
        $dob = substr($dob, 0, 4);
        if (strlen($dob) !== 4) {
            return '';
        }

        $urutPart = str_pad((string) $urut, 5, '0', STR_PAD_LEFT);
        if (strlen($urutPart) > 5) {
            $urutPart = substr($urutPart, -5);
        }

        return $dob . $urutPart;
    }

    /**
     * Tanggal register sebagai DDMMYY (format barcode lama).
     *
     * @param mixed $permohonan
     * @return string
     */
    public static function registerDateDmy($permohonan): string
    {
        if (!$permohonan) {
            return '';
        }

        try {
            if (!empty($permohonan->tglregister_permohonan_uji_klinik)) {
                return \Carbon\Carbon::parse($permohonan->tglregister_permohonan_uji_klinik)->format('dmy');
            }
            if (!empty($permohonan->created_at)) {
                return \Carbon\Carbon::parse($permohonan->created_at)->format('dmy');
            }
        } catch (\Throwable $e) {
            return '';
        }

        return '';
    }

    /**
     * Format lama (DDMMYY + urut 4 digit) untuk mencocokkan hasil TMS yang sudah tersimpan.
     *
     * @param mixed $permohonan
     * @return string
     */
    public static function labelRegisterCodeLegacy($permohonan): string
    {
        $datePart = self::registerDateDmy($permohonan);
        $urut = self::specimenUrutNumber($permohonan);
        if ($datePart === '' || $urut < 1) {
            return '';
        }

        return $datePart . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Kanonikalisasi nama jenis spesimen (termasuk tindakan sampling).
     */
    public static function normalizeSpecimenType(string $jenis): string
    {
        $j = strtolower(trim($jenis));
        if ($j === '') {
            return 'Lainnya';
        }

        if (strpos($j, 'blood cell') !== false || strpos($j, 'sel darah') !== false) {
            return 'Blood Cell';
        }
        if (
            strpos($j, 'plasma') !== false
            && (
                strpos($j, 'naf') !== false
                || strpos($j, 'na-f') !== false
                || strpos($j, 'fluoride') !== false
                || strpos($j, 'fluorida') !== false
            )
        ) {
            return 'Plasma NaF';
        }
        if (strpos($j, 'plasma') !== false) {
            return 'Plasma';
        }
        if (strpos($j, 'serum') !== false) {
            return 'Serum';
        }
        if (strpos($j, 'darah') !== false) {
            return 'Darah';
        }
        if (strpos($j, 'urin') !== false) {
            return 'Urine';
        }
        if (
            strpos($j, 'feses') !== false
            || strpos($j, 'faec') !== false
            || strpos($j, 'feces') !== false
        ) {
            return 'Feses';
        }
        if (strpos($j, 'swab') !== false) {
            return 'Swab';
        }

        foreach (self::specimenTypeOrder() as $canonical) {
            if (strcasecmp($canonical, trim($jenis)) === 0) {
                return $canonical;
            }
        }

        return 'Lainnya';
    }

    /**
     * Digit ke-10 barcode = kode jenis spesimen.
     * 1 Darah, 2 Serum, 3 Plasma, 8 Plasma NaF, 4 Urine, 5 Feses, 6 Swab, 7 Blood Cell, 9 Lainnya.
     */
    public static function specimenTypeDigit(string $jenis): string
    {
        $map = [
            'Darah' => '1',
            'Serum' => '2',
            'Plasma' => '3',
            'Plasma NaF' => '8',
            'Urine' => '4',
            'Feses' => '5',
            'Swab' => '6',
            'Blood Cell' => '7',
            'Lainnya' => '9',
        ];
        $canonical = self::normalizeSpecimenType($jenis);

        if (self::$plasmaSampleDigitAlias && $canonical === 'Plasma NaF') {
            return '3';
        }

        return $map[$canonical] ?? '9';
    }

    /**
     * Balikkan digit jenis barcode ke nama kanonik.
     */
    public static function specimenTypeFromDigit(string $digit): string
    {
        if (self::$plasmaSampleDigitAlias && $digit === '8') {
            return 'Plasma';
        }

        $map = [
            '1' => 'Darah',
            '2' => 'Serum',
            '3' => 'Plasma',
            '8' => 'Plasma NaF',
            '4' => 'Urine',
            '5' => 'Feses',
            '6' => 'Swab',
            '7' => 'Blood Cell',
            '9' => 'Lainnya',
        ];

        return $map[$digit] ?? 'Lainnya';
    }

    /**
     * Pecah nomor sampel & nomor pasien dari data permohonan klinik.
     * Nomor spesimen untuk TMS = {nomer_sampel}.{nomer_pasien}
     *
     * Auto  : noregister "2687/1140" → sampel=2687, pasien=1140 → "2687.1140"
     * Manual: nomor_lab_manual / nomor_spesimen_manual → sama urutan (lab=sampel, spesimen=pasien)
     *
     * @param  mixed  $permohonan
     * @return array{nomer_sampel: string, nomer_pasien: string, nomer_spesimen: string}
     */
    public static function specimenNumberParts($permohonan): array
    {
        $nomerSampel = '';
        $nomerPasien = '';

        if ($permohonan) {
            try {
                $settings = \Smt\Masterweb\Models\KlinikNumberSettings::getSettings();
                $manualLab = trim((string) ($permohonan->nomor_lab_manual ?? ''));
                $manualSpesimen = trim((string) ($permohonan->nomor_spesimen_manual ?? ''));

                if (
                    $settings
                    && (int) ($settings->is_nomor_lab_manual ?? 0) === 1
                    && (int) ($settings->is_nomor_spesimen_manual ?? 0) === 1
                    && $manualLab !== ''
                    && $manualSpesimen !== ''
                ) {
                    // Selaras tampilan "No. Lab / Spesimen": lab = sampel, spesimen = pasien
                    $nomerSampel = $manualLab;
                    $nomerPasien = $manualSpesimen;
                } else {
                    $noregister = trim((string) ($permohonan->noregister_permohonan_uji_klinik ?? ''));
                    if ($noregister !== '' && strpos($noregister, '/') !== false) {
                        $parts = array_map('trim', explode('/', $noregister, 2));
                        $nomerSampel = $parts[0] ?? '';
                        $nomerPasien = $parts[1] ?? '';
                    } elseif ($noregister !== '') {
                        $nomerSampel = $noregister;
                    } elseif (!empty($permohonan->nourut_permohonan_uji_klinik)) {
                        $nomerSampel = (string) $permohonan->nourut_permohonan_uji_klinik;
                    }

                    if ($nomerPasien === '') {
                        $nomerPasien = trim((string) (
                            optional($permohonan->pasien)->nourut_pasien
                            ?? $permohonan->nomor_spesimen_manual
                            ?? ''
                        ));
                    }
                }
            } catch (\Throwable $e) {
                // biarkan kosong
            }
        }

        $nomerSampel = trim((string) $nomerSampel);
        $nomerPasien = trim((string) $nomerPasien);

        $nomerSpesimen = '';
        if ($nomerSampel !== '' && $nomerPasien !== '') {
            $nomerSpesimen = $nomerSampel . '.' . $nomerPasien;
        } elseif ($nomerSampel !== '') {
            $nomerSpesimen = $nomerSampel;
        } elseif ($nomerPasien !== '') {
            $nomerSpesimen = $nomerPasien;
        }

        return [
            'nomer_sampel' => $nomerSampel,
            'nomer_pasien' => $nomerPasien,
            'nomer_spesimen' => $nomerSpesimen,
        ];
    }

    /**
     * Nomor spesimen format TMS: nomer_sampel.nomer_pasien
     *
     * @param  mixed  $permohonan
     * @return string
     */
    public static function specimenNumber($permohonan): string
    {
        return self::specimenNumberParts($permohonan)['nomer_spesimen'];
    }

    /**
     * Kandidat sample_id dari data permohonan klinik.
     *
     * @return string[]
     */
    public static function sampleIdCandidates($permohonan): array
    {
        $raw = [
            self::labelRegisterCode($permohonan),
            self::labelRegisterCodeLegacy($permohonan),
            self::specimenNumber($permohonan),
            $permohonan->nomor_lab_manual ?? null,
            $permohonan->noregister_permohonan_uji_klinik ?? null,
            $permohonan->nomor_spesimen_manual ?? null,
            $permohonan->nomer_lab ?? null,
        ];

        $out = [];
        foreach ($raw as $v) {
            $v = trim((string) $v);
            if ($v === '') {
                continue;
            }
            $out[] = $v;
            // tanpa leading zero
            $stripped = ltrim($v, '0');
            if ($stripped !== '' && $stripped !== $v) {
                $out[] = $stripped;
            }
            // padded 4/5 digit
            if (ctype_digit($v)) {
                $out[] = str_pad($v, 4, '0', STR_PAD_LEFT);
                $out[] = str_pad($v, 5, '0', STR_PAD_LEFT);
            }
        }

        $base = self::labelRegisterCode($permohonan);
        if ($base !== '') {
            foreach (self::specimenTypeOrder() as $jenis) {
                $out[] = self::barcodeForSpesimen($base, $jenis, true);
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Urutan tampilan/group spesimen TMS.
     *
     * @return string[]
     */
    public static function specimenTypeOrder(): array
    {
        return ['Darah', 'Blood Cell', 'Serum', 'Plasma', 'Plasma NaF', 'Urine', 'Feses', 'Swab', 'Lainnya'];
    }

    /**
     * Resolusi jenis spesimen kanonik dari master parameter satuan klinik.
     *
     * @param  mixed  $satuanKlinik
     * @param  mixed  $permohonanOrIsHaji
     * @return string
     */
    public static function resolveJenisSpesimen($satuanKlinik, $permohonanOrIsHaji = 0): string
    {
        if (!$satuanKlinik) {
            return 'Lainnya';
        }

        try {
            $raw = \Smt\Masterweb\Helpers\Smt::pickJenisSampelRawForContext(
                $satuanKlinik,
                \Smt\Masterweb\Helpers\Smt::resolvePermohonanIsHaji($permohonanOrIsHaji)
            );
            $list = \Smt\Masterweb\Helpers\Smt::canonicalJenisListFromParameterRawTypes([$raw]);
            if (!empty($list)) {
                return (string) $list[0];
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return 'Lainnya';
    }

    /**
     * Resolusi jenis spesimen dari id_parameter_tms (via ms_parameter_satuan_klinik).
     *
     * @param  int  $tmsId
     * @param  mixed  $permohonanOrIsHaji
     * @return string
     */
    public static function resolveJenisSpesimenByTmsId(int $tmsId, $permohonanOrIsHaji = 0): string
    {
        if ($tmsId <= 0) {
            return 'Lainnya';
        }

        $fromMaster = self::jenisSampelFromParameterTms($tmsId);
        if ($fromMaster !== '') {
            return $fromMaster;
        }

        try {
            $rows = \Smt\Masterweb\Models\ParameterSatuanKlinik::query()
                ->where('id_parameter_tms', $tmsId)
                ->whereNull('deleted_at')
                ->get(['jenis_sampel', 'jenis_sampel_haji', 'name_parameter_satuan_klinik']);

            foreach ($rows as $row) {
                $resolved = self::resolveJenisSpesimen($row, $permohonanOrIsHaji);
                if ($resolved !== 'Lainnya') {
                    return $resolved;
                }
                $fromName = self::jenisSampelFromLabel((string) ($row->name_parameter_satuan_klinik ?? ''));
                if (in_array($fromName, ['Urine', 'Feses', 'Swab'], true)) {
                    return $fromName;
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return 'Lainnya';
    }

    /**
     * Jenis sampel untuk Make Order TMS.
     * Urutan: urin/feses/swab dari nama klinik → jenis HAJI vs NON HAJI di satuan klinik
     * → jenis alat di ms_parameter_tms.
     *
     * @param  mixed  $param  PermohonanUjiParameterKlinik atau yang punya parametersatuanklinik
     * @param  string|null  $tmsJenisSampel  ms_parameter_tms.jenis_sampel
     * @param  mixed  $permohonanOrIsHaji
     * @return string
     */
    public static function resolveJenisSpesimenForTmsOrder($param, $tmsJenisSampel = null, $permohonanOrIsHaji = 0): string
    {
        $satuan = null;
        if (is_object($param) && isset($param->parametersatuanklinik)) {
            $satuan = $param->parametersatuanklinik;
        } elseif (is_object($param) && method_exists($param, 'parametersatuanklinik')) {
            $satuan = $param->parametersatuanklinik;
        }

        $klinikName = '';
        if (is_object($satuan) && isset($satuan->name_parameter_satuan_klinik)) {
            $klinikName = (string) $satuan->name_parameter_satuan_klinik;
        }

        $fromName = self::jenisSampelFromLabel($klinikName);
        if (in_array($fromName, ['Urine', 'Feses', 'Swab'], true)) {
            return $fromName;
        }

        $fromSatuan = self::resolveJenisSpesimen($satuan, $permohonanOrIsHaji);
        if ($fromSatuan !== 'Lainnya') {
            return $fromSatuan;
        }

        $fromTms = self::canonicalJenisSampelOrEmpty($tmsJenisSampel);
        if ($fromTms !== '') {
            return $fromTms;
        }

        if ($fromName !== '' && $fromName !== 'Lainnya') {
            return $fromName;
        }

        return 'Lainnya';
    }

    /**
     * Jenis sampel tersimpan di master alat TMS.
     *
     * @param  int  $tmsId
     * @return string  kosong jika belum diisi
     */
    public static function jenisSampelFromParameterTms($tmsId): string
    {
        $tmsId = (int) $tmsId;
        if ($tmsId <= 0) {
            return '';
        }

        try {
            $row = \Smt\Masterweb\Models\ParameterTms::query()
                ->where('id_parameter_tms', $tmsId)
                ->whereNull('deleted_at')
                ->first(['jenis_sampel']);
            return self::canonicalJenisSampelOrEmpty($row ? $row->jenis_sampel : null);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * @param  mixed  $value
     * @return string
     */
    public static function canonicalJenisSampelOrEmpty($value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }
        $canonical = self::normalizeSpecimenType($raw);
        if ($canonical === '' || $canonical === 'Lainnya') {
            return '';
        }

        return $canonical;
    }

    /**
     * Petunjuk jenis dari label (nama parameter klinik / TMS).
     *
     * @param  string  $label
     * @return string
     */
    public static function jenisSampelFromLabel($label): string
    {
        $n = strtolower(trim((string) $label));
        if ($n === '') {
            return '';
        }

        if (strpos($n, 'blood cell') !== false || strpos($n, 'sel darah') !== false || strpos($n, 'hba1c') !== false || preg_match('/\ba1c\b/', $n)) {
            return 'Blood Cell';
        }
        if (strpos($n, 'urin') !== false) {
            return 'Urine';
        }
        if (strpos($n, 'feses') !== false || strpos($n, 'feces') !== false || strpos($n, 'faec') !== false) {
            return 'Feses';
        }
        if (strpos($n, 'swab') !== false) {
            return 'Swab';
        }

        return '';
    }

    /**
     * Kelompokkan id parameter TMS menurut jenis spesimen.
     * Key = jenis spesimen, value = list id_parameter_tms (unik, urut).
     *
     * @param  array<int, array{jenis: string}>  $metaByTmsId
     * @param  int[]  $tmsIds
     * @return array<string, int[]>
     */
    public static function groupTmsIdsBySpesimen(array $metaByTmsId, array $tmsIds): array
    {
        $groups = [];
        foreach ($tmsIds as $tmsId) {
            $tmsId = (int) $tmsId;
            if ($tmsId <= 0) {
                continue;
            }
            $jenis = trim((string) ($metaByTmsId[$tmsId]['jenis'] ?? 'Lainnya'));
            if ($jenis === '') {
                $jenis = 'Lainnya';
            }
            if (!isset($groups[$jenis])) {
                $groups[$jenis] = [];
            }
            if (!in_array($tmsId, $groups[$jenis], true)) {
                $groups[$jenis][] = $tmsId;
            }
        }

        $ordered = [];
        foreach (self::specimenTypeOrder() as $jenis) {
            if (!empty($groups[$jenis])) {
                $ordered[$jenis] = $groups[$jenis];
                unset($groups[$jenis]);
            }
        }
        foreach ($groups as $jenis => $ids) {
            $ordered[$jenis] = $ids;
        }

        return $ordered;
    }

    /**
     * Barcode 10 digit: {DDMM lahir}{nomor spesimen 5}{kode jenis 1}.
     * Contoh: lahir 26/02/1992, spesimen 3902, Serum → 2602039022
     *
     * $multiGroup dipertahankan agar pemanggil lama tidak rusak; jenis selalu di-encode.
     */
    public static function barcodeForSpesimen(string $baseBarcode, string $jenis, bool $multiGroup = true): string
    {
        $baseBarcode = trim($baseBarcode);
        $jenis = trim($jenis);

        if ($baseBarcode !== '' && preg_match('#^(.+)/([^/]+)$#', $baseBarcode, $m)) {
            $baseBarcode = trim((string) $m[1]);
            if ($jenis === '') {
                $jenis = trim((string) $m[2]);
            }
        }

        $digits = preg_replace('/\D+/', '', $baseBarcode) ?? '';
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) >= 10) {
            $digits = substr($digits, 0, 9);
        }

        return str_pad($digits, 9, '0', STR_PAD_LEFT) . self::specimenTypeDigit($jenis);
    }

    /**
     * Barcode 10 digit dari permohonan + jenis spesimen.
     *
     * @param mixed $permohonan
     */
    public static function barcodeForPermohonan($permohonan, string $jenis): string
    {
        return self::barcodeForSpesimen(self::labelRegisterCode($permohonan), $jenis, true);
    }

    /**
     * Pecah sample_id / barcode 10 digit alat TMS.
     * Format: {DDMM lahir 4}{nomor spesimen 5}{digit jenis 1}.
     *
     * @param  string  $sampleId
     * @return array{digits:string,base9:?string,dob4:?string,urut5:?string,urut_int:int,type1:?string}|null
     */
    public static function parseSampleIdBarcode(string $sampleId): ?array
    {
        $digits = preg_replace('/\D+/', '', trim($sampleId)) ?? '';
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10) {
            return [
                'digits' => $digits,
                'base9' => substr($digits, 0, 9),
                'dob4' => substr($digits, 0, 4),
                'urut5' => substr($digits, 4, 5),
                'urut_int' => (int) substr($digits, 4, 5),
                'type1' => substr($digits, 9, 1),
            ];
        }

        if (strlen($digits) === 9) {
            return [
                'digits' => $digits,
                'base9' => $digits,
                'dob4' => substr($digits, 0, 4),
                'urut5' => substr($digits, 4, 5),
                'urut_int' => (int) substr($digits, 4, 5),
                'type1' => null,
            ];
        }

        if (strlen($digits) <= 5) {
            $urut5 = str_pad($digits, 5, '0', STR_PAD_LEFT);

            return [
                'digits' => $digits,
                'base9' => null,
                'dob4' => null,
                'urut5' => $urut5,
                'urut_int' => (int) $digits,
                'type1' => null,
            ];
        }

        return null;
    }

    /**
     * Apakah dua sample_id / kode_barcode merujuk spesimen yang sama.
     *
     * @param  string  $a
     * @param  string  $b
     * @return bool
     */
    public static function sampleIdDigitsEquivalent(string $a, string $b): bool
    {
        $da = preg_replace('/\D+/', '', trim($a)) ?? '';
        $db = preg_replace('/\D+/', '', trim($b)) ?? '';
        if ($da === '' || $db === '') {
            return false;
        }
        if ($da === $db) {
            return true;
        }
        if (strlen($da) === 10 && strlen($db) === 10 && substr($da, 0, 9) === substr($db, 0, 9)) {
            return self::plasmaTypeDigitsEquivalent(substr($da, 9, 1), substr($db, 9, 1));
        }
        if (strlen($da) === 10 && strlen($db) === 9 && substr($da, 0, 9) === $db) {
            return true;
        }
        if (strlen($db) === 10 && strlen($da) === 9 && substr($db, 0, 9) === $da) {
            return true;
        }

        return false;
    }

    /**
     * Hitung kode_barcode order dari permohonan SimaLab + sample_id alat.
     *
     * @param  \Smt\Masterweb\Models\OrderTms|null  $order
     * @param  string  $sampleId
     * @return string
     */
    public static function computedBarcodeForOrder($order, string $sampleId = ''): string
    {
        if (!$order) {
            return '';
        }

        $perm = $order->relationLoaded('permohonanUjiKlinik')
            ? $order->permohonanUjiKlinik
            : $order->permohonanUjiKlinik()->first();
        if (!$perm) {
            return '';
        }

        $parsed = $sampleId !== '' ? self::parseSampleIdBarcode($sampleId) : null;
        if ($parsed && $parsed['type1'] !== null) {
            $fromDigit = self::specimenTypeFromDigit($parsed['type1']);
            if ($fromDigit !== 'Lainnya') {
                $bc = self::barcodeForPermohonan($perm, $fromDigit);
                if ($bc !== '') {
                    return $bc;
                }
            }
        }

        $jenis = trim((string) ($order->jenis_sampel ?? ''));
        if ($jenis === '') {
            $jenis = self::inferJenisSpesimenFromOrder($order, $perm);
        }
        if ($jenis === '' || $jenis === 'Lainnya') {
            $jenis = 'Serum';
        }

        return self::barcodeForPermohonan($perm, $jenis);
    }

    /**
     * Cocokkan sample_id alat ke order TMS (kode_barcode DB atau barcode hasil hitung permohonan).
     *
     * @param  \Smt\Masterweb\Models\OrderTms|null  $order
     * @param  string  $sampleId
     * @return bool
     */
    public static function orderMatchesSampleId($order, string $sampleId): bool
    {
        $sampleId = trim($sampleId);
        if ($sampleId === '' || !$order) {
            return false;
        }

        $stored = trim((string) ($order->kode_barcode ?? ''));
        if ($stored !== '' && self::sampleIdDigitsEquivalent($stored, $sampleId)) {
            return true;
        }

        $computed = self::computedBarcodeForOrder($order, $sampleId);
        if ($computed !== '' && self::sampleIdDigitsEquivalent($computed, $sampleId)) {
            return true;
        }

        $perm = $order->relationLoaded('permohonanUjiKlinik')
            ? $order->permohonanUjiKlinik
            : $order->permohonanUjiKlinik()->first();
        if (!$perm) {
            return false;
        }

        foreach (self::specimenTypeOrder() as $jenis) {
            if ($jenis === 'Lainnya') {
                continue;
            }
            $bc = self::barcodeForPermohonan($perm, $jenis);
            if ($bc !== '' && self::sampleIdDigitsEquivalent($bc, $sampleId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Barcode yang seharusnya disimpan pada order untuk sample_id ini.
     *
     * @param  \Smt\Masterweb\Models\OrderTms|null  $order
     * @param  string  $sampleId
     * @return string
     */
    public static function resolveBarcodeForOrderFromSampleId($order, string $sampleId): string
    {
        $sampleId = trim($sampleId);
        if ($sampleId === '' || !$order) {
            return '';
        }

        $stored = trim((string) ($order->kode_barcode ?? ''));
        if ($stored !== '' && self::sampleIdDigitsEquivalent($stored, $sampleId)) {
            return $stored;
        }

        $computed = self::computedBarcodeForOrder($order, $sampleId);
        if ($computed !== '') {
            return $computed;
        }

        $digits = preg_replace('/\D+/', '', $sampleId) ?? '';

        if (strlen($digits) === 10) {
            if (self::$plasmaSampleDigitAlias) {
                $last = substr($digits, 9, 1);
                if ($last === '3' || $last === '8') {
                    return substr($digits, 0, 9) . '3';
                }
            }

            return $digits;
        }

        return $stored;
    }

    /**
     * Relasi eager load standar untuk pencocokan sample_id.
     *
     * @return array
     */
    public static function sampleIdMatchEagerLoad(): array
    {
        return [
            'permohonanUjiKlinik.pasien',
            'details' => function ($q) {
                $q->whereNull('deleted_at');
            },
        ];
    }

    /**
     * Cari order lewat permohonan (nomor spesimen) bila kode_barcode DB tidak cocok.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $base
     * @param  string  $sampleId
     * @return \Illuminate\Support\Collection
     */
    public static function fetchOrdersBySampleIdViaPermohonan($base, string $sampleId)
    {
        $parsed = self::parseSampleIdBarcode($sampleId);
        if (!$parsed || $parsed['urut_int'] < 1) {
            return collect();
        }

        $urut = $parsed['urut_int'];
        $urutPadded = str_pad((string) $urut, 5, '0', STR_PAD_LEFT);

        return (clone $base)
            ->with(self::sampleIdMatchEagerLoad())
            ->whereHas('permohonanUjiKlinik', function ($pq) use ($urut, $urutPadded) {
                $pq->whereNull('deleted_at');
                $pq->where(function ($q) use ($urut, $urutPadded) {
                    $q->where('nourut_permohonan_uji_klinik', $urut)
                        ->orWhere('nomor_spesimen_manual', (string) $urut)
                        ->orWhere('nomor_spesimen_manual', $urutPadded)
                        ->orWhereRaw(
                            'CAST(NULLIF(TRIM(SUBSTRING_INDEX(noregister_permohonan_uji_klinik, ?, 1)), ?) AS UNSIGNED) = ?',
                            ['/', '', $urut]
                        );
                });
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->filter(function ($order) use ($sampleId) {
                return self::orderMatchesSampleId($order, $sampleId);
            })
            ->values();
    }

    /**
     * Cari order TMS dari sample_id alat: kode_barcode DB dulu, lalu permohonan SimaLab.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $base
     * @param  string  $sampleId
     * @return \Illuminate\Support\Collection
     */
    public static function fetchOrdersMatchingSampleId($base, string $sampleId)
    {
        $sampleId = trim($sampleId);
        if ($sampleId === '') {
            return collect();
        }

        $byBarcode = self::applyBarcodeLookup(clone $base, $sampleId)
            ->with(self::sampleIdMatchEagerLoad())
            ->orderBy('created_at', 'asc')
            ->get();

        if ($byBarcode->isNotEmpty()) {
            return $byBarcode;
        }

        return self::fetchOrdersBySampleIdViaPermohonan($base, $sampleId);
    }

    /**
     * Filter order TMS by sample_id: exact, suffix lama /Jenis, basis 9 digit, atau nomor spesimen 1–5 digit.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $sampleId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyBarcodeLookup($query, string $sampleId)
    {
        $sampleId = trim($sampleId);
        if ($sampleId === '') {
            return $query;
        }

        return $query->where(function ($q) use ($sampleId) {
            $q->where('kode_barcode', $sampleId)
                ->orWhere('kode_barcode', 'like', $sampleId . '/%');

            $digits = preg_replace('/\D+/', '', $sampleId) ?? '';
            if ($digits === '') {
                return;
            }

            if (strlen($digits) === 10) {
                $base9 = substr($digits, 0, 9);
                $q->orWhere('kode_barcode', $digits)
                    ->orWhere('kode_barcode', 'like', $base9 . '_');
                if (self::$plasmaSampleDigitAlias) {
                    $last = substr($digits, 9, 1);
                    if ($last === '3' || $last === '8') {
                        $q->orWhere('kode_barcode', $base9 . '3')
                            ->orWhere('kode_barcode', $base9 . '8');
                    }
                }
                return;
            }

            if (strlen($digits) === 9) {
                $q->orWhere('kode_barcode', 'like', $digits . '_');
                return;
            }

            if (strlen($digits) <= 5) {
                $urut = str_pad($digits, 5, '0', STR_PAD_LEFT);
                $q->orWhere('kode_barcode', 'like', '____' . $urut . '_');
            }
        });
    }

    /**
     * Infer jenis spesimen dari detail order / suffix barcode.
     *
     * @param  \Smt\Masterweb\Models\OrderTms|null  $order
     * @param  mixed  $permohonanOrIsHaji
     * @return string
     */
    public static function inferJenisSpesimenFromOrder($order, $permohonanOrIsHaji = 0): string
    {
        $stored = trim((string) ($order->jenis_sampel ?? ''));
        if ($stored !== '') {
            return $stored;
        }

        $jenis = null;
        if ($order && $order->relationLoaded('details')) {
            foreach ($order->details as $d) {
                $satuan = null;
                if ($d->relationLoaded('permohonanUjiParameterKlinik')) {
                    $satuan = optional($d->permohonanUjiParameterKlinik)->parametersatuanklinik;
                } elseif (method_exists($d, 'permohonanUjiParameterKlinik')) {
                    $satuan = optional($d->permohonanUjiParameterKlinik)->parametersatuanklinik;
                }

                $resolved = 'Lainnya';
                $tmsJenis = '';
                if ($d->relationLoaded('parameterTms') && $d->parameterTms) {
                    $tmsJenis = (string) ($d->parameterTms->jenis_sampel ?? '');
                    $fromMasterName = self::jenisSampelFromLabel((string) ($d->parameterTms->name_parameter_tms ?? ''));
                    if ($fromMasterName !== '') {
                        $resolved = $fromMasterName;
                    }
                }
                if ($resolved === 'Lainnya') {
                    $resolved = self::resolveJenisSpesimenForTmsOrder(
                        (object) ['parametersatuanklinik' => $satuan],
                        $tmsJenis,
                        $permohonanOrIsHaji
                    );
                }

                if ($jenis === null || $jenis === 'Lainnya') {
                    $jenis = $resolved;
                }
                if ($resolved !== 'Lainnya') {
                    return $resolved;
                }
            }
        }

        $barcode = trim((string) ($order->kode_barcode ?? ''));
        if ($barcode !== '') {
            $digits = preg_replace('/\D+/', '', $barcode) ?? '';
            if (strlen($digits) === 10) {
                $fromDigit = self::specimenTypeFromDigit(substr($digits, -1));
                if ($fromDigit !== 'Lainnya') {
                    return $fromDigit;
                }
            }
            foreach (self::specimenTypeOrder() as $candidate) {
                if ($candidate === 'Lainnya') {
                    continue;
                }
                if (substr($barcode, -strlen('/' . $candidate)) === '/' . $candidate) {
                    return $candidate;
                }
            }
        }

        return $jenis ?: 'Lainnya';
    }

    /**
     * Cocokkan order untuk terima hasil TMS (id_order_tms tidak wajib).
     *
     * Urutan:
     * 1. barcode / sample_id
     * 2. order masih punya slot kosong, prioritas antrian
     * 3. prioritas tray + pos dari payload (cocok penuh > kosong di DB > mismatch tetap antrian barcode)
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $base
     * @param  string  $sampleId
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @param  bool  $includeAllMatching  true = sertakan order yang slot-nya sudah terisi (replay / hasil terlambat)
     * @return array{orders:\Illuminate\Support\Collection, matched_by:string|null, error:string|null, all_filled?:bool}
     */
    public static function resolvePendingOrdersByBarcodeTrayPos($base, string $sampleId, $tray = null, $pos = null, $includeAllMatching = false)
    {
        $tray = self::normalizeTrayPosValue($tray);
        $pos = self::normalizeTrayPosValue($pos);
        $sampleId = trim($sampleId);

        if ($sampleId === '') {
            return ['orders' => collect(), 'matched_by' => null, 'error' => null];
        }

        $locationHint = self::formatOrderLocationHint($sampleId, $tray, $pos);

        $allMatching = self::fetchOrdersMatchingSampleId($base, $sampleId);
        $viaPermohonan = $allMatching->isNotEmpty()
            && !self::applyBarcodeLookup(clone $base, $sampleId)->exists();

        if ($allMatching->isEmpty()) {
            return [
                'orders' => collect(),
                'matched_by' => null,
                'error' => 'Order tidak ditemukan untuk ' . $locationHint . '.',
            ];
        }

        $matching = $includeAllMatching
            ? $allMatching->values()
            : $allMatching->filter(function ($order) {
                return self::orderHasEmptyDetailSlot($order);
            })->values();

        if (!$includeAllMatching && $matching->isEmpty()) {
            return [
                'orders' => collect(),
                'matched_by' => null,
                'error' => null,
                'all_filled' => true,
            ];
        }

        $candidates = self::sortOrdersForResultMatch($matching, $tray, $pos);
        $parts = ['sample_id'];
        if ($viaPermohonan) {
            $parts[] = 'permohonan';
        }
        if ($includeAllMatching) {
            $parts[] = 'merged';
        }

        if ($tray !== null) {
            $parts[] = 'tray';
        }
        if ($pos !== null) {
            $parts[] = 'pos';
        }

        if ($candidates->count() === 1) {
            return [
                'orders' => $candidates,
                'matched_by' => implode('+', $parts),
                'error' => null,
            ];
        }

        return [
            'orders' => $candidates,
            'matched_by' => implode('+', $parts) . '+queue',
            'error' => null,
        ];
    }

    /**
     * @param  string  $sampleId
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @return string
     */
    public static function formatOrderLocationHint(string $sampleId, $tray = null, $pos = null): string
    {
        $hint = 'barcode ' . trim($sampleId);
        if ($tray !== null) {
            $hint .= ' tray ' . $tray;
        }
        if ($pos !== null) {
            $hint .= ' pos ' . $pos;
        }

        return $hint;
    }

    /**
     * @param  mixed  $value
     * @return bool
     */
    public static function detailValueIsEmpty($value): bool
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' || $value === '-';
    }

    /**
     * Order masih bisa menerima hasil jika ada detail yang kosong.
     *
     * @param  \Smt\Masterweb\Models\OrderTms|null  $order
     * @return bool
     */
    public static function orderHasEmptyDetailSlot($order): bool
    {
        if (!$order) {
            return false;
        }

        if (!$order->relationLoaded('details')) {
            return true;
        }

        foreach ($order->details as $detail) {
            if (self::detailValueIsEmpty($detail->value ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  \Smt\Masterweb\Models\OrderTms|null  $order
     * @return int
     */
    public static function countFilledDetails($order): int
    {
        if (!$order || !$order->relationLoaded('details')) {
            return 0;
        }

        $filled = 0;
        foreach ($order->details as $detail) {
            if (!self::detailValueIsEmpty($detail->value ?? '')) {
                $filled++;
            }
        }

        return $filled;
    }

    /**
     * Antrian penerima hasil: slot kosong lebih dulu, lalu dibuat paling awal.
     *
     * @param  \Smt\Masterweb\Models\OrderTms  $a
     * @param  \Smt\Masterweb\Models\OrderTms  $b
     * @return int
     */
    public static function compareOrdersForResultQueue($a, $b): int
    {
        $filledA = self::countFilledDetails($a);
        $filledB = self::countFilledDetails($b);
        if ($filledA !== $filledB) {
            return $filledA <=> $filledB;
        }

        $execA = (bool) ($a->is_executed ?? false);
        $execB = (bool) ($b->is_executed ?? false);
        if ($execA !== $execB) {
            return ($execA ? 1 : 0) <=> ($execB ? 1 : 0);
        }

        $timeA = $a->created_at ? $a->created_at->getTimestamp() : 0;
        $timeB = $b->created_at ? $b->created_at->getTimestamp() : 0;
        if ($timeA !== $timeB) {
            return $timeA <=> $timeB;
        }

        return strcmp((string) $a->id_order_tms, (string) $b->id_order_tms);
    }

    /**
     * Skor kecocokan tray/pos order vs payload alat.
     * Cocok penuh = 10, belum diisi di DB = 5, mismatch = 0 per field.
     *
     * @param  \Smt\Masterweb\Models\OrderTms|null  $order
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @return int
     */
    public static function scoreTrayPosMatch($order, $tray = null, $pos = null): int
    {
        if (!$order) {
            return 0;
        }

        $score = 0;

        if ($tray !== null) {
            $orderTray = self::normalizeTrayPosValue($order->tray);
            if ($orderTray === $tray) {
                $score += 10;
            } elseif ($orderTray === null) {
                $score += 5;
            }
        }

        if ($pos !== null) {
            $orderPos = self::normalizeTrayPosValue($order->pos);
            if ($orderPos === $pos) {
                $score += 10;
            } elseif ($orderPos === null) {
                $score += 5;
            }
        }

        return $score;
    }

    /**
     * Antrian penerima hasil dengan prioritas tray/pos, lalu antrian slot kosong.
     *
     * @param  \Smt\Masterweb\Models\OrderTms  $a
     * @param  \Smt\Masterweb\Models\OrderTms  $b
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @return int
     */
    public static function compareOrdersForResultMatch($a, $b, $tray = null, $pos = null): int
    {
        $scoreA = self::scoreTrayPosMatch($a, $tray, $pos);
        $scoreB = self::scoreTrayPosMatch($b, $tray, $pos);
        if ($scoreA !== $scoreB) {
            return $scoreB <=> $scoreA;
        }

        return self::compareOrdersForResultQueue($a, $b);
    }

    /**
     * @param  \Illuminate\Support\Collection  $orders
     * @param  string|null  $tray
     * @param  string|null  $pos
     * @return \Illuminate\Support\Collection
     */
    public static function sortOrdersForResultMatch($orders, $tray = null, $pos = null)
    {
        return $orders->sort(function ($a, $b) use ($tray, $pos) {
            return self::compareOrdersForResultMatch($a, $b, $tray, $pos);
        })->values();
    }

    /**
     * @param  \Illuminate\Support\Collection  $orders
     * @return \Illuminate\Support\Collection
     */
    public static function sortOrdersForResultQueue($orders)
    {
        return $orders->sort(function ($a, $b) {
            return self::compareOrdersForResultQueue($a, $b);
        })->values();
    }

    /**
     * @param  mixed  $value
     * @return string|null
     */
    public static function normalizeTrayPosValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }
}
