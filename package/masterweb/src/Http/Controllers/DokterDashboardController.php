<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Helpers\BakuMutuPermohonanKlinikHelper;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\Wilayah;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;

class DokterDashboardController extends Controller
{
    /** @var array|null In-request baku mutu lookup by id_baku_mutu */
    private $bakuMutuById = null;

    /** @var array param|jenis => list BakuMutu (untuk fallback gender/umur/haji) */
    private $bakuMutuByParamJenis = [];

    /** @var bool|null apakah kolom snapshot baku mutu ada di tabel hasil */
    private $hasBakuMutuSnapshotColumns = null;

    /** @var array parameter_satuan_id => 'id'|'en' */
    private $parameterFormatMap = [];

    /** @var array In-request geocode cache */
    private $geocodeRuntimeCache = [];

    /** @var array|null Index nama wilayah dinormalisasi => list record */
    private $wilayahNameIndex = null;

    /** @var array Cache resolve alamat => wilayah */
    private $alamatWilayahCache = [];

    /** @var array|null Indeks koordinat wilayah Magelang (by_code / by_name) */
    private $wilayahCoordsIndex = null;

    /** @var string|null Filter gender: L | P | null (semua) */
    private $filterGender = null;

    /** @var int|null Usia minimum (tahun) */
    private $filterUmurMin = null;

    /** @var int|null Usia maksimum (tahun) */
    private $filterUmurMax = null;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display dashboard analisis hasil klinik per wilayah.
     */
    public function index(Request $request)
    {
        $tipeWilayah = $request->get('tipe_wilayah', 'KEC');
        $wilayahId = $request->get('wilayah_id', null);
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $parameterIdsInput = $request->get('parameter_ids', '');
        $parameterIds = [];
        if (!empty($parameterIdsInput) && trim($parameterIdsInput) !== '') {
            $parameterIds = array_filter(array_map('trim', explode(',', $parameterIdsInput)));
            $parameterIds = array_values($parameterIds);
        }
        $tipeParameter = $request->get('tipe_parameter', 'satuan');
        $viewType = $request->get('view_type', 'both');

        $genderInput = strtoupper(trim((string) $request->get('gender', '')));
        $this->filterGender = in_array($genderInput, ['L', 'P'], true) ? $genderInput : null;

        $umurMinRaw = $request->get('umur_min', '');
        $umurMaxRaw = $request->get('umur_max', '');
        $this->filterUmurMin = ($umurMinRaw !== '' && $umurMinRaw !== null && is_numeric($umurMinRaw))
            ? max(0, (int) $umurMinRaw)
            : null;
        $this->filterUmurMax = ($umurMaxRaw !== '' && $umurMaxRaw !== null && is_numeric($umurMaxRaw))
            ? max(0, (int) $umurMaxRaw)
            : null;
        if ($this->filterUmurMin !== null && $this->filterUmurMax !== null && $this->filterUmurMin > $this->filterUmurMax) {
            $tmp = $this->filterUmurMin;
            $this->filterUmurMin = $this->filterUmurMax;
            $this->filterUmurMax = $tmp;
        }

        $wilayahOptions = $this->getWilayahOptions($tipeWilayah);

        $parameterSatuans = ParameterSatuanKlinik::whereNull('deleted_at')
            ->orderBy('name_parameter_satuan_klinik', 'ASC')
            ->get(['id_parameter_satuan_klinik', 'name_parameter_satuan_klinik']);
        $parameterPakets = ParameterPaketKlinik::whereNull('deleted_at')
            ->orderBy('name_parameter_paket_klinik', 'ASC')
            ->get(['id_parameter_paket_klinik', 'name_parameter_paket_klinik']);

        // Satu query set: permohonan terakhir per pasien (bukan N+1 × 3)
        $latestPermohonanIds = $this->getLatestPermohonanPerPasien($tipeWilayah, $wilayahId, $bulan, $tahun);
        $this->warmBakuMutuCache();

        $statistics = $this->getStatistics($latestPermohonanIds, $parameterIds, $tipeParameter);

        $mapData = [];
        $scatterData = ['data' => [], 'labels' => []];
        if ($viewType === 'map' || $viewType === 'both') {
            $mapData = $this->getMapData($latestPermohonanIds, $parameterIds, $tipeParameter);
        }
        if ($viewType === 'scatter' || $viewType === 'both') {
            $scatterData = $this->getScatterData($latestPermohonanIds, $parameterIds, $tipeParameter);
        }

        $filterGender = $this->filterGender;
        $filterUmurMin = $this->filterUmurMin;
        $filterUmurMax = $this->filterUmurMax;

        return view('masterweb::module.dokter.dashboard', compact(
            'tipeWilayah',
            'wilayahId',
            'wilayahOptions',
            'statistics',
            'mapData',
            'scatterData',
            'bulan',
            'tahun',
            'parameterIds',
            'tipeParameter',
            'parameterSatuans',
            'parameterPakets',
            'viewType',
            'filterGender',
            'filterUmurMin',
            'filterUmurMax'
        ));
    }

    /**
     * Get wilayah options berdasarkan tipe
     */
    private function getWilayahOptions($tipeWilayah)
    {
        if ($tipeWilayah === 'luar_daerah') {
            return Wilayah::where('tipe', 'KAB')
                ->where('wilayah_kode', 'NOT LIKE', '3308%')
                ->orderBy('wilayah', 'ASC')
                ->get(['id_wilayah', 'wilayah', 'wilayah_kode', 'tipe']);
        }

        if ($tipeWilayah === 'DESA') {
            return Wilayah::where('tipe', 'DESA')
                ->where('wilayah_kode', 'LIKE', '3308%')
                ->orderBy('wilayah', 'ASC')
                ->get(['id_wilayah', 'wilayah', 'wilayah_kode', 'tipe']);
        }

        if ($tipeWilayah === 'DUSUN') {
            return Wilayah::where('tipe', 'DUSUN')
                ->where('wilayah_kode', 'LIKE', '3308%')
                ->orderBy('wilayah', 'ASC')
                ->get(['id_wilayah', 'wilayah', 'wilayah_kode', 'tipe']);
        }

        return Wilayah::where('tipe', 'KEC')
            ->where('wilayah_kode', 'LIKE', '3308%')
            ->orderBy('wilayah', 'ASC')
            ->get(['id_wilayah', 'wilayah', 'wilayah_kode', 'tipe']);
    }

    /**
     * Latest permohonan per pasien — single SQL + filter wilayah
     * (wilayah_id pasien ATAU fallback dari alamat teks berpemisah koma).
     */
    private function getLatestPermohonanPerPasien($tipeWilayah, $wilayahId, $bulan, $tahun)
    {
        $bindings = [];
        $dateSql = '';
        if ($bulan && $tahun) {
            $dateSql = ' AND (
                (k.tglpengujian_permohonan_uji_klinik IS NOT NULL
                    AND YEAR(k.tglpengujian_permohonan_uji_klinik) = ?
                    AND MONTH(k.tglpengujian_permohonan_uji_klinik) = ?)
                OR (k.tglpengujian_permohonan_uji_klinik IS NULL
                    AND YEAR(k.created_at) = ?
                    AND MONTH(k.created_at) = ?)
            ) ';
            $bindings[] = (int) $tahun;
            $bindings[] = (int) $bulan;
            $bindings[] = (int) $tahun;
            $bindings[] = (int) $bulan;
        }

        // LEFT JOIN wilayah: pasien tanpa wilayah_id tetap diambil untuk fallback alamat
        $sql = "
            SELECT
                id_permohonan_uji_klinik,
                pasien_permohonan_uji_klinik,
                wilayah_id,
                alamat_pasien,
                wilayah_kode,
                wilayah_nama,
                wilayah_tipe
            FROM (
                SELECT
                    k.id_permohonan_uji_klinik,
                    k.pasien_permohonan_uji_klinik,
                    p.wilayah_id,
                    p.alamat_pasien,
                    w.wilayah_kode,
                    w.wilayah AS wilayah_nama,
                    w.tipe AS wilayah_tipe,
                    ROW_NUMBER() OVER (
                        PARTITION BY k.pasien_permohonan_uji_klinik
                        ORDER BY COALESCE(k.tglpengujian_permohonan_uji_klinik, k.created_at) DESC,
                                 k.created_at DESC
                    ) AS rn
                FROM tb_permohonan_uji_klinik_2 k
                INNER JOIN ms_pasien p ON p.id_pasien = k.pasien_permohonan_uji_klinik
                LEFT JOIN ms_wilayah w ON w.id_wilayah = p.wilayah_id
                WHERE k.deleted_at IS NULL
                {$dateSql}
            ) ranked
            WHERE rn = 1
        ";

        $rows = DB::select($sql, $bindings);
        $ids = [];
        foreach ($rows as $row) {
            $resolved = $this->resolvePasienWilayah(
                $row->wilayah_id,
                $row->alamat_pasien,
                $row->wilayah_kode,
                $row->wilayah_nama,
                $row->wilayah_tipe
            );
            if (!$resolved) {
                continue;
            }
            if (!$this->wilayahMatchesFilter($resolved, $tipeWilayah, $wilayahId)) {
                continue;
            }
            $ids[] = $row->id_permohonan_uji_klinik;
        }

        return $ids;
    }

    /**
     * Bangun index nama wilayah (Magelang + luar) untuk matching alamat.
     */
    private function warmWilayahNameIndex()
    {
        if ($this->wilayahNameIndex !== null) {
            return;
        }

        $this->wilayahNameIndex = [
            'DESA' => [],
            'KEC' => [],
            'DUSUN' => [],
            'KAB' => [],
        ];

        Wilayah::query()
            ->whereIn('tipe', ['DESA', 'KEC', 'DUSUN', 'KAB'])
            ->get(['id_wilayah', 'wilayah', 'wilayah_kode', 'tipe'])
            ->each(function ($w) {
                $norm = $this->normalizeWilayahToken($w->wilayah);
                if ($norm === '') {
                    return;
                }
                $tipe = $w->tipe;
                if (!isset($this->wilayahNameIndex[$tipe])) {
                    $this->wilayahNameIndex[$tipe] = [];
                }
                if (!isset($this->wilayahNameIndex[$tipe][$norm])) {
                    $this->wilayahNameIndex[$tipe][$norm] = [];
                }
                $this->wilayahNameIndex[$tipe][$norm][] = [
                    'id' => $w->id_wilayah,
                    'nama' => $w->wilayah,
                    'kode' => $w->wilayah_kode,
                    'tipe' => $w->tipe,
                ];
            });
    }

    private function normalizeWilayahToken($text)
    {
        $text = strtolower(trim((string) $text));
        if ($text === '') {
            return '';
        }
        $text = preg_replace('/^(desa|kelurahan|kel\.|kecamatan|kec\.|kabupaten|kab\.|kota|dusun)\s+/iu', '', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    /**
     * Resolve wilayah pasien: prioritaskan wilayah_id spesifik,
     * fallback parse alamat (dipisah koma).
     */
    private function resolvePasienWilayah($wilayahId, $alamat, $existingKode = null, $existingNama = null, $existingTipe = null)
    {
        $fromId = null;
        if ($wilayahId && $existingKode && $existingNama && $existingTipe) {
            $fromId = [
                'id' => $wilayahId,
                'nama' => $existingNama,
                'kode' => $existingKode,
                'tipe' => $existingTipe,
            ];
        } elseif ($wilayahId) {
            $w = Wilayah::find($wilayahId);
            if ($w) {
                $fromId = [
                    'id' => $w->id_wilayah,
                    'nama' => $w->wilayah,
                    'kode' => $w->wilayah_kode,
                    'tipe' => $w->tipe,
                ];
            }
        }

        $fromAlamat = $this->resolveWilayahFromAlamat($alamat);

        // Pakai alamat jika belum ada wilayah, atau wilayah hanya KAB/PROV, atau alamat lebih spesifik (DESA)
        if ($fromAlamat) {
            if (!$fromId) {
                return $fromAlamat;
            }
            if (in_array($fromId['tipe'], ['KAB', 'PROV'], true)) {
                return $fromAlamat;
            }
            if ($fromId['tipe'] === 'KEC' && in_array($fromAlamat['tipe'], ['DESA', 'DUSUN'], true)) {
                return $fromAlamat;
            }
        }

        return $fromId ?: $fromAlamat;
    }

    /**
     * Ambil wilayah dari alamat teks (segmen dipisah koma).
     * Contoh: "Jl ..., Desa X, Kecamatan Y, Kabupaten Magelang"
     */
    private function resolveWilayahFromAlamat($alamat)
    {
        $alamat = trim((string) $alamat);
        if ($alamat === '' || $alamat === '-') {
            return null;
        }

        $cacheKey = md5(mb_strtolower($alamat));
        if (array_key_exists($cacheKey, $this->alamatWilayahCache)) {
            return $this->alamatWilayahCache[$cacheKey];
        }

        $this->warmWilayahNameIndex();
        $parts = array_values(array_filter(array_map(function ($p) {
            return $this->normalizeWilayahToken($p);
        }, explode(',', $alamat))));

        if (empty($parts)) {
            return $this->alamatWilayahCache[$cacheKey] = null;
        }

        $matchedKec = null;
        foreach ($parts as $part) {
            if (isset($this->wilayahNameIndex['KEC'][$part])) {
                $candidates = $this->wilayahNameIndex['KEC'][$part];
                $matchedKec = $this->preferMagelangCandidate($candidates) ?: $candidates[0];
            }
        }

        $preferTipeOrder = ['DESA', 'DUSUN', 'KEC', 'KAB'];
        foreach ($preferTipeOrder as $tipe) {
            foreach ($parts as $part) {
                if (!isset($this->wilayahNameIndex[$tipe][$part])) {
                    continue;
                }
                $candidates = $this->wilayahNameIndex[$tipe][$part];
                if ($matchedKec && in_array($tipe, ['DESA', 'DUSUN'], true)) {
                    $kecPrefix = substr((string) $matchedKec['kode'], 0, 6);
                    $filtered = array_values(array_filter($candidates, function ($c) use ($kecPrefix) {
                        return strpos((string) $c['kode'], $kecPrefix) === 0;
                    }));
                    if (!empty($filtered)) {
                        $candidates = $filtered;
                    }
                }
                $picked = $this->preferMagelangCandidate($candidates) ?: $candidates[0];
                return $this->alamatWilayahCache[$cacheKey] = $picked;
            }
        }

        return $this->alamatWilayahCache[$cacheKey] = ($matchedKec ?: null);
    }

    private function preferMagelangCandidate(array $candidates)
    {
        foreach ($candidates as $c) {
            if (strpos((string) $c['kode'], '3308') === 0) {
                return $c;
            }
        }
        return null;
    }

    private function wilayahMatchesFilter(array $resolved, $tipeWilayah, $wilayahId)
    {
        $kode = (string) ($resolved['kode'] ?? '');
        $isMagelang = strpos($kode, '3308') === 0;

        if ($tipeWilayah === 'luar_daerah') {
            return !$isMagelang;
        }

        if (!$isMagelang) {
            return false;
        }

        if (!$wilayahId) {
            return true;
        }

        $filter = Wilayah::find($wilayahId);
        if (!$filter) {
            return true;
        }

        if ($tipeWilayah === 'DESA' || $tipeWilayah === 'DUSUN') {
            return (string) $resolved['id'] === (string) $wilayahId
                || strpos($kode, (string) $filter->wilayah_kode) === 0;
        }

        if ($tipeWilayah === 'KEC') {
            $prefix = substr((string) $filter->wilayah_kode, 0, 6);
            return strpos($kode, $prefix) === 0
                || (string) $resolved['id'] === (string) $wilayahId;
        }

        return true;
    }

    /**
     * Resolve parameter satuan IDs from filter (satuan / paket).
     * null = no filter; [] = filter that matches nothing.
     */
    /**
     * Resolve parameter satuan IDs from filter (satuan / paket).
     * null = no filter; [] = filter that matches nothing.
     */
    private function resolveParameterSatuanIds(array $parameterIds, $tipeParameter)
    {
        if (empty($parameterIds)) {
            return null;
        }

        $filterIds = array_map(function ($id) {
            return is_numeric($id) ? (int) $id : $id;
        }, $parameterIds);

        if ($tipeParameter === 'paket') {
            $paketJenisIds = ParameterPaketJenisKlinik::whereIn('parameter_paket_klinik_id', $filterIds)
                ->whereNull('deleted_at')
                ->pluck('id_parameter_paket_jenis_klinik')
                ->toArray();

            return ParameterSatuanPaketKlinik::whereIn('parameter_paket_jenis_klinik', $paketJenisIds)
                ->whereNull('deleted_at')
                ->pluck('parameter_satuan_klinik')
                ->unique()
                ->values()
                ->all();
        }

        return $filterIds;
    }

    private function applyParameterFilter($query, array $parameterIds, $tipeParameter)
    {
        $satuanIds = $this->resolveParameterSatuanIds($parameterIds, $tipeParameter);
        if ($satuanIds === null) {
            return $query;
        }
        if (empty($satuanIds)) {
            return $query->whereRaw('1 = 0');
        }
        return $query->whereIn('tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', $satuanIds);
    }

    /**
     * Filter hasil berdasarkan jenis kelamin dan/atau rentang usia pasien.
     */
    private function applyPasienDemografiFilter($query)
    {
        if ($this->filterGender === 'L') {
            $query->whereRaw("UPPER(TRIM(COALESCE(ms_pasien.gender_pasien, ''))) IN ('L', 'M', 'MALE')");
        } elseif ($this->filterGender === 'P') {
            $query->whereRaw("UPPER(TRIM(COALESCE(ms_pasien.gender_pasien, ''))) IN ('P', 'F', 'FEMALE')");
        }

        $ageExpr = 'TIMESTAMPDIFF(YEAR, ms_pasien.tgllahir_pasien, COALESCE(tb_permohonan_uji_klinik_2.tglpengujian_permohonan_uji_klinik, tb_permohonan_uji_klinik_2.created_at))';

        if ($this->filterUmurMin !== null) {
            $query->whereRaw($ageExpr . ' >= ?', [$this->filterUmurMin]);
        }
        if ($this->filterUmurMax !== null) {
            $query->whereRaw($ageExpr . ' <= ?', [$this->filterUmurMax]);
        }

        return $query;
    }

    /**
     * Preload baku mutu + number_format parameter sekali per request.
     */
    private function warmBakuMutuCache()
    {
        if ($this->bakuMutuById !== null) {
            return;
        }

        $this->bakuMutuById = [];
        $this->bakuMutuByParamJenis = [];
        $this->parameterFormatMap = ParameterSatuanKlinik::query()
            ->whereNull('deleted_at')
            ->pluck('number_format', 'id_parameter_satuan_klinik')
            ->map(function ($fmt) {
                return ($fmt === 'id') ? 'id' : 'en';
            })
            ->all();

        BakuMutu::query()
            ->select([
                'id_baku_mutu',
                'parameter_satuan_klinik_id',
                'parameter_jenis_klinik_id',
                'equal',
                'min',
                'max',
                'is_khusus_baku_mutu',
                'gender_baku_mutu',
                'minimal_umur_baku_mutu',
                'maksimal_umur_baku_mutu',
                'is_haji',
                'is_normal',
            ])
            ->orderBy('id_baku_mutu')
            ->get()
            ->each(function ($bm) {
                $this->bakuMutuById[$bm->id_baku_mutu] = $bm;
                $key = $bm->parameter_satuan_klinik_id . '|' . $bm->parameter_jenis_klinik_id;
                if (!isset($this->bakuMutuByParamJenis[$key])) {
                    $this->bakuMutuByParamJenis[$key] = [];
                }
                $this->bakuMutuByParamJenis[$key][] = $bm;
            });
    }

    /**
     * Parse angka mengikuti number_format parameter (sama seperti parseNumberInput di JS).
     * ID: titik = pemisah ribuan, koma = desimal → "1.010" = 1010, "1,025" = 1.025
     * EN: koma = ribuan, titik = desimal → "1.010" = 1.01
     */
    private function parseNumericValue($value, $format = 'en')
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/\s+/u', '', $value);
        if ($value === '' || $value === '-') {
            return null;
        }

        // Ambil token numerik pertama jika ada teks lain
        if (preg_match('/-?\d+(?:[.,]\d+)*/', $value, $m)) {
            $value = $m[0];
        }

        $format = ($format === 'id') ? 'id' : 'en';

        if ($format === 'id') {
            // 1.234,56 → 1234.56 ; 1.010 → 1010
            if (strpos($value, ',') !== false) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace('.', '', $value);
            }
        } else {
            // 1,234.56 → 1234.56
            $value = str_replace(',', '', $value);
        }

        $value = preg_replace('/[^\d.\-]/', '', $value);
        if ($value === '' || $value === '-' || $value === '.' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * Format angka untuk tampilan sesuai number_format parameter.
     */
    private function formatNumericValue($value, $format = 'en', $decimals = null)
    {
        if ($value === null || !is_numeric($value)) {
            return '-';
        }

        $value = (float) $value;
        if ($decimals === null) {
            // Nilai BJ-style ID (1005, 1020): tampilkan sebagai 1.005 / 1.020
            if ($format === 'id' && abs($value) >= 100 && abs($value - round($value)) < 0.0001) {
                $decimals = 0;
            } else {
                $asString = rtrim(rtrim(sprintf('%.6F', $value), '0'), '.');
                $decimals = (strpos($asString, '.') !== false)
                    ? strlen(substr(strrchr($asString, '.'), 1))
                    : 0;
                $decimals = max(0, min(4, $decimals));
                if ($decimals < 2 && abs($value) < 100 && abs($value - round($value)) > 0.00001) {
                    $decimals = 2;
                }
            }
        }

        if ($format === 'id') {
            return number_format($value, $decimals, ',', '.');
        }

        return number_format($value, $decimals, '.', ',');
    }

    private function getParameterNumberFormat($parameterSatuanKlinikId)
    {
        $this->warmBakuMutuCache();
        return $this->parameterFormatMap[$parameterSatuanKlinikId] ?? 'en';
    }

    /**
     * Get statistics data - per pasien, menggunakan hasil terakhir
     */
    private function getStatistics(array $latestPermohonanIds, $parameterIds, $tipeParameter)
    {
        if (empty($latestPermohonanIds)) {
            return [
                'total_samples' => 0,
                'total_results' => 0,
                'normal_count' => 0,
                'average' => 0,
                'max' => 0,
                'min' => 0,
                'abnormal_count' => 0,
                'abnormal_percentage' => 0,
                'parameter_stats' => [],
            ];
        }

        $query = PermohonanUjiParameterKlinik::query()
            ->join('tb_permohonan_uji_klinik_2', 'tb_permohonan_uji_parameter_klinik.permohonan_uji_klinik', '=', 'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik')
            ->join('ms_pasien', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik', '=', 'ms_pasien.id_pasien')
            ->leftJoin('ms_parameter_satuan_klinik', 'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', '=', 'ms_parameter_satuan_klinik.id_parameter_satuan_klinik')
            ->whereIn('tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik', $latestPermohonanIds)
            ->whereNotNull('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik')
            ->where('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik', '!=', '-')
            ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at')
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at');

        $query = $this->applyParameterFilter($query, $parameterIds ?: [], $tipeParameter);
        $query = $this->applyPasienDemografiFilter($query);

        $results = $query->select(array_merge([
            'tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik',
            'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
            'tb_permohonan_uji_parameter_klinik.jenis_parameter_klinik_id',
            'tb_permohonan_uji_parameter_klinik.baku_mutu_permohonan_uji_parameter_klinik',
            'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2.is_haji',
            'ms_pasien.tgllahir_pasien',
            'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
            'ms_parameter_satuan_klinik.number_format',
            DB::raw('TIMESTAMPDIFF(YEAR, ms_pasien.tgllahir_pasien, COALESCE(tb_permohonan_uji_klinik_2.tglpengujian_permohonan_uji_klinik, tb_permohonan_uji_klinik_2.created_at)) as umur_tahun'),
        ], $this->bakuMutuSelectExtras()))->get();

        $totalSamples = $results->unique('pasien_permohonan_uji_klinik')->count();

        $parameterBuckets = [];
        $allResultsNumeric = [];
        $totalAbnormalCount = 0;

        foreach ($results as $result) {
            $hasil = $result->hasil_permohonan_uji_parameter_klinik;
            $parameterId = $result->parameter_satuan_klinik;
            $format = ($result->number_format === 'id') ? 'id' : $this->getParameterNumberFormat($parameterId);

            $hasilNumeric = $this->parseNumericValue($hasil, $format);
            if ($hasilNumeric === null) {
                continue;
            }

            $allResultsNumeric[] = $hasilNumeric;

            if (!isset($parameterBuckets[$parameterId])) {
                $parameterBuckets[$parameterId] = [
                    'parameter_name' => $result->name_parameter_satuan_klinik ?? ('Parameter #' . $parameterId),
                    'number_format' => $format,
                    'total' => 0,
                    'below' => 0,
                    'above' => 0,
                    'other_abnormal' => 0,
                    'normal' => 0,
                ];
            }

            $parameterBuckets[$parameterId]['total']++;
            $status = $this->classifyVsBakuMutu($result);

            if ($status === 'below') {
                $parameterBuckets[$parameterId]['below']++;
                $totalAbnormalCount++;
            } elseif ($status === 'above') {
                $parameterBuckets[$parameterId]['above']++;
                $totalAbnormalCount++;
            } elseif ($status === 'abnormal') {
                $parameterBuckets[$parameterId]['other_abnormal']++;
                $totalAbnormalCount++;
            } else {
                $parameterBuckets[$parameterId]['normal']++;
            }
        }

        $parameterStats = [];
        foreach ($parameterBuckets as $parameterId => $bucket) {
            if ($bucket['total'] === 0) {
                continue;
            }

            $abnormalCount = $bucket['below'] + $bucket['above'] + $bucket['other_abnormal'];
            $parameterStats[] = [
                'parameter_id' => $parameterId,
                'parameter_name' => $bucket['parameter_name'],
                'number_format' => $bucket['number_format'],
                'below_count' => $bucket['below'],
                'above_count' => $bucket['above'],
                'other_abnormal_count' => $bucket['other_abnormal'],
                'abnormal_count' => $abnormalCount,
                'normal_count' => $bucket['normal'],
                'abnormal_percentage' => round(($abnormalCount / $bucket['total']) * 100, 2),
                'normal_percentage' => round(($bucket['normal'] / $bucket['total']) * 100, 2),
                'total_results' => $bucket['total'],
            ];
        }

        usort($parameterStats, function ($a, $b) {
            if ($b['abnormal_count'] != $a['abnormal_count']) {
                return $b['abnormal_count'] <=> $a['abnormal_count'];
            }
            if ($b['abnormal_percentage'] != $a['abnormal_percentage']) {
                return $b['abnormal_percentage'] <=> $a['abnormal_percentage'];
            }
            return strcmp($a['parameter_name'], $b['parameter_name']);
        });

        $totalResults = count($allResultsNumeric);
        $totalNormalCount = max(0, $totalResults - $totalAbnormalCount);
        $overallAbnormalPercentage = $totalResults > 0
            ? ($totalAbnormalCount / $totalResults) * 100
            : 0;

        return [
            'total_samples' => $totalSamples,
            'total_results' => $totalResults,
            'normal_count' => $totalNormalCount,
            'average' => 0,
            'max' => 0,
            'min' => 0,
            'abnormal_count' => $totalAbnormalCount,
            'abnormal_percentage' => round($overallAbnormalPercentage, 2),
            'parameter_stats' => $parameterStats,
        ];
    }

    /**
     * Kolom tambahan untuk pemutusan baku mutu (gender + snapshot bila ada).
     */
    private function bakuMutuSelectExtras()
    {
        $cols = [
            'ms_pasien.gender_pasien',
        ];

        if ($this->hasBakuMutuSnapshotColumns === null) {
            $this->hasBakuMutuSnapshotColumns = BakuMutuPermohonanKlinikHelper::hasSnapshotColumns();
        }

        if ($this->hasBakuMutuSnapshotColumns) {
            $cols[] = 'tb_permohonan_uji_parameter_klinik.min_baku_mutu_permohonan_uji_parameter_klinik';
            $cols[] = 'tb_permohonan_uji_parameter_klinik.max_baku_mutu_permohonan_uji_parameter_klinik';
            $cols[] = 'tb_permohonan_uji_parameter_klinik.equal_baku_mutu_permohonan_uji_parameter_klinik';
        }

        return $cols;
    }

    /**
     * Ambil baku mutu yang benar untuk 1 hasil:
     * 1) Snapshot min/max/equal di baris hasil (sudah sesuai gender/umur saat input)
     * 2) ID baku mutu tersimpan di baris hasil
     * 3) Fallback: cocokkan gender + umur + haji (sama seperti BakuMutuPermohonanKlinikHelper)
     */
    private function resolveBakuMutuForResult($parameterKlinik)
    {
        $this->warmBakuMutuCache();

        $parameterSatuanKlinikId = is_object($parameterKlinik) ? $parameterKlinik->parameter_satuan_klinik : ($parameterKlinik['parameter_satuan_klinik'] ?? null);
        $jenisParameterKlinikId = is_object($parameterKlinik) ? $parameterKlinik->jenis_parameter_klinik_id : ($parameterKlinik['jenis_parameter_klinik_id'] ?? null);

        // Snapshot di hasil sudah diputus saat analisa (gender/umur benar)
        $snapshot = $this->resolveBakuMutuFromSnapshot($parameterKlinik);
        if ($snapshot !== null) {
            return $snapshot;
        }

        $storedBmId = is_object($parameterKlinik)
            ? ($parameterKlinik->baku_mutu_permohonan_uji_parameter_klinik ?? null)
            : ($parameterKlinik['baku_mutu_permohonan_uji_parameter_klinik'] ?? null);

        $umur = is_object($parameterKlinik) ? ($parameterKlinik->umur_tahun ?? null) : ($parameterKlinik['umur_tahun'] ?? null);
        $isHaji = is_object($parameterKlinik) ? (int) ($parameterKlinik->is_haji ?? 0) : (int) ($parameterKlinik['is_haji'] ?? 0);
        $genderRaw = is_object($parameterKlinik)
            ? ($parameterKlinik->gender_pasien ?? null)
            : ($parameterKlinik['gender_pasien'] ?? null);
        $gender = BakuMutuPermohonanKlinikHelper::normalizePasienGender($genderRaw);

        if (is_string($storedBmId) && preg_match('/^[0-9a-f\-]{36}$/i', $storedBmId) && isset($this->bakuMutuById[$storedBmId])) {
            $stored = $this->bakuMutuById[$storedBmId];
            if ($this->bakuMutuMatchesPasien($stored, $gender, $umur)) {
                return $stored;
            }
            // BM tersimpan tidak cocok gender/umur → resolve ulang di bawah
        }

        if (!$parameterSatuanKlinikId || !$jenisParameterKlinikId) {
            return null;
        }

        $key = $parameterSatuanKlinikId . '|' . $jenisParameterKlinikId;
        $candidates = collect($this->bakuMutuByParamJenis[$key] ?? []);
        if ($candidates->isEmpty()) {
            return null;
        }

        // Filter haji dulu, lalu pakai helper gender+umur yang sama dengan layar analis
        $byHaji = $candidates->filter(function ($bm) use ($isHaji) {
            return (int) ($bm->is_haji ?? 0) === $isHaji;
        });
        if ($byHaji->isEmpty()) {
            $byHaji = $candidates;
        }

        $matched = BakuMutuPermohonanKlinikHelper::resolveForPasien($byHaji, $gender, $umur);
        if ($matched) {
            return $matched;
        }

        return BakuMutuPermohonanKlinikHelper::matchByGenderFallback($byHaji, $gender);
    }

    /**
     * Apakah baris baku mutu cocok dengan gender/umur/haji pasien.
     */
    private function bakuMutuMatchesPasien($bm, $gender, $umur)
    {
        if ($bm === null) {
            return false;
        }

        $bmGender = $bm->gender_baku_mutu ?? null;
        if ($bmGender !== null && $bmGender !== '' && $gender !== null && $bmGender !== $gender) {
            return false;
        }

        if ((int) ($bm->is_khusus_baku_mutu ?? 0) === 1 && $umur !== null && $umur !== '') {
            $minAge = $bm->minimal_umur_baku_mutu;
            $maxAge = $bm->maksimal_umur_baku_mutu;
            if ($minAge !== null && $maxAge !== null) {
                if ((float) $umur < (float) $minAge || (float) $umur > (float) $maxAge) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Bangun objek baku mutu dari snapshot kolom di hasil (jika ada & terisi).
     */
    private function resolveBakuMutuFromSnapshot($parameterKlinik)
    {
        if ($this->hasBakuMutuSnapshotColumns === null) {
            $this->hasBakuMutuSnapshotColumns = BakuMutuPermohonanKlinikHelper::hasSnapshotColumns();
        }
        if (!$this->hasBakuMutuSnapshotColumns) {
            return null;
        }

        $min = is_object($parameterKlinik)
            ? ($parameterKlinik->min_baku_mutu_permohonan_uji_parameter_klinik ?? null)
            : ($parameterKlinik['min_baku_mutu_permohonan_uji_parameter_klinik'] ?? null);
        $max = is_object($parameterKlinik)
            ? ($parameterKlinik->max_baku_mutu_permohonan_uji_parameter_klinik ?? null)
            : ($parameterKlinik['max_baku_mutu_permohonan_uji_parameter_klinik'] ?? null);
        $equal = is_object($parameterKlinik)
            ? ($parameterKlinik->equal_baku_mutu_permohonan_uji_parameter_klinik ?? null)
            : ($parameterKlinik['equal_baku_mutu_permohonan_uji_parameter_klinik'] ?? null);

        $hasMin = $min !== null && $min !== '';
        $hasMax = $max !== null && $max !== '';
        $hasEqual = $equal !== null && $equal !== '' && $equal != '0';

        // Snapshot multi-range (koma) tidak dipakai di sini — biarkan resolve master
        if ($hasMin && strpos((string) $min, ',') !== false) {
            return null;
        }
        if ($hasMax && strpos((string) $max, ',') !== false) {
            return null;
        }
        if ($hasEqual && strpos((string) $equal, ',') !== false) {
            return null;
        }

        if (!$hasMin && !$hasMax && !$hasEqual) {
            return null;
        }

        return (object) [
            'min' => $hasMin ? $min : null,
            'max' => $hasMax ? $max : null,
            'equal' => $hasEqual ? $equal : null,
            'from_snapshot' => true,
        ];
    }

    /**
     * Klasifikasi hasil vs baku mutu: below|above|abnormal|normal|skip
     */
    private function classifyVsBakuMutu($parameterKlinik)
    {
        $parameterSatuanKlinikId = is_object($parameterKlinik) ? $parameterKlinik->parameter_satuan_klinik : ($parameterKlinik['parameter_satuan_klinik'] ?? null);
        $jenisParameterKlinikId = is_object($parameterKlinik) ? $parameterKlinik->jenis_parameter_klinik_id : ($parameterKlinik['jenis_parameter_klinik_id'] ?? null);
        $hasil = is_object($parameterKlinik) ? $parameterKlinik->hasil_permohonan_uji_parameter_klinik : ($parameterKlinik['hasil_permohonan_uji_parameter_klinik'] ?? null);

        if (!$parameterSatuanKlinikId || !$jenisParameterKlinikId || $hasil === null || $hasil === '') {
            return 'skip';
        }

        $format = $this->getParameterNumberFormat($parameterSatuanKlinikId);
        $hasilNumeric = $this->parseNumericValue($hasil, $format);
        if ($hasilNumeric === null) {
            return 'skip';
        }

        $bakuMutu = $this->resolveBakuMutuForResult($parameterKlinik);
        if (!$bakuMutu) {
            return 'normal';
        }

        $hasEqual = $bakuMutu->equal !== null && $bakuMutu->equal !== '' && $bakuMutu->equal != '0';
        if ($hasEqual) {
            $equalNumeric = $this->parseNumericValue($bakuMutu->equal, $format);
            if ($equalNumeric !== null && abs($hasilNumeric - $equalNumeric) > 0.0000001) {
                return 'abnormal';
            }
        }

        $hasMin = $bakuMutu->min !== null && $bakuMutu->min !== '';
        $hasMax = $bakuMutu->max !== null && $bakuMutu->max !== '';

        if ($hasMin && $hasMax) {
            $min = $this->parseNumericValue($bakuMutu->min, $format);
            $max = $this->parseNumericValue($bakuMutu->max, $format);
            if ($min !== null && $max !== null) {
                if ($hasilNumeric < $min) {
                    return 'below';
                }
                if ($hasilNumeric > $max) {
                    return 'above';
                }
            }
        } elseif ($hasMin) {
            $min = $this->parseNumericValue($bakuMutu->min, $format);
            if ($min !== null && $hasilNumeric < $min) {
                return 'below';
            }
        } elseif ($hasMax) {
            $max = $this->parseNumericValue($bakuMutu->max, $format);
            if ($max !== null && $hasilNumeric > $max) {
                return 'above';
            }
        }

        return 'normal';
    }

    /**
     * Check if result is abnormal — respect number_format parameter (EN/ID).
     */
    private function isAbnormal($parameterKlinik)
    {
        $status = $this->classifyVsBakuMutu($parameterKlinik);
        return in_array($status, ['below', 'above', 'abnormal'], true);
    }

    /**
     * Data peta: hanya wilayah yang punya hasil melewati baku mutu.
     * Marker diurutkan menurut jumlah pelanggaran; popup menampilkan parameter teratas.
     */
    private function getMapData(array $latestPermohonanIds, $parameterIds, $tipeParameter)
    {
        if (empty($latestPermohonanIds)) {
            return [];
        }

        $query = PermohonanUjiParameterKlinik::query()
            ->join('tb_permohonan_uji_klinik_2', 'tb_permohonan_uji_parameter_klinik.permohonan_uji_klinik', '=', 'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik')
            ->join('ms_pasien', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik', '=', 'ms_pasien.id_pasien')
            ->leftJoin('ms_wilayah', 'ms_pasien.wilayah_id', '=', 'ms_wilayah.id_wilayah')
            ->leftJoin('ms_parameter_satuan_klinik', 'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', '=', 'ms_parameter_satuan_klinik.id_parameter_satuan_klinik')
            ->whereIn('tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik', $latestPermohonanIds)
            ->whereNotNull('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik')
            ->where('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik', '!=', '-')
            ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at')
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at');

        $query = $this->applyParameterFilter($query, $parameterIds ?: [], $tipeParameter);
        $query = $this->applyPasienDemografiFilter($query);

        $rows = $query->select(array_merge([
            'ms_pasien.wilayah_id as pasien_wilayah_id',
            'ms_pasien.alamat_pasien',
            'ms_wilayah.id_wilayah',
            'ms_wilayah.wilayah',
            'ms_wilayah.wilayah_kode',
            'ms_wilayah.tipe as wilayah_tipe',
            'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2.is_haji',
            'tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik',
            'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
            'tb_permohonan_uji_parameter_klinik.jenis_parameter_klinik_id',
            'tb_permohonan_uji_parameter_klinik.baku_mutu_permohonan_uji_parameter_klinik',
            'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
            DB::raw('TIMESTAMPDIFF(YEAR, ms_pasien.tgllahir_pasien, COALESCE(tb_permohonan_uji_klinik_2.tglpengujian_permohonan_uji_klinik, tb_permohonan_uji_klinik_2.created_at)) as umur_tahun'),
        ], $this->bakuMutuSelectExtras()))->get();

        $byWilayah = [];
        foreach ($rows as $row) {
            $format = $this->getParameterNumberFormat($row->parameter_satuan_klinik);
            $hasilNumeric = $this->parseNumericValue($row->hasil_permohonan_uji_parameter_klinik, $format);
            if ($hasilNumeric === null) {
                continue;
            }

            $resolved = $this->resolvePasienWilayah(
                $row->pasien_wilayah_id,
                $row->alamat_pasien,
                $row->wilayah_kode,
                $row->wilayah,
                $row->wilayah_tipe
            );
            if (!$resolved) {
                continue;
            }

            $wid = $resolved['id'];
            if (!isset($byWilayah[$wid])) {
                $byWilayah[$wid] = [
                    'id' => $wid,
                    'nama' => $resolved['nama'],
                    'kode' => $resolved['kode'],
                    'tipe' => $resolved['tipe'],
                    'pasien_ids' => [],
                    'pasien_abnormal_ids' => [],
                    'pengujian_ids' => [],
                    'abnormal_count' => 0,
                    'below_count' => 0,
                    'above_count' => 0,
                    'normal_count' => 0,
                    'total_results' => 0,
                    'param_abnormal' => [],
                ];
            }

            $byWilayah[$wid]['total_results']++;
            $byWilayah[$wid]['pasien_ids'][$row->pasien_permohonan_uji_klinik] = true;
            $byWilayah[$wid]['pengujian_ids'][$row->id_permohonan_uji_klinik] = true;

            // Pakai baku mutu tersimpan di hasil (sama seperti statistik & layar analis)
            $status = $this->classifyVsBakuMutu($row);
            if ($status === 'below') {
                $byWilayah[$wid]['below_count']++;
                $byWilayah[$wid]['abnormal_count']++;
            } elseif ($status === 'above') {
                $byWilayah[$wid]['above_count']++;
                $byWilayah[$wid]['abnormal_count']++;
            } elseif ($status === 'abnormal') {
                $byWilayah[$wid]['abnormal_count']++;
            } else {
                $byWilayah[$wid]['normal_count']++;
                continue;
            }

            $byWilayah[$wid]['pasien_abnormal_ids'][$row->pasien_permohonan_uji_klinik] = true;
            $paramName = $row->name_parameter_satuan_klinik ?: ('Parameter #' . $row->parameter_satuan_klinik);
            if (!isset($byWilayah[$wid]['param_abnormal'][$paramName])) {
                $byWilayah[$wid]['param_abnormal'][$paramName] = 0;
            }
            $byWilayah[$wid]['param_abnormal'][$paramName]++;
        }

        $mapData = [];
        foreach ($byWilayah as $wilayah) {
            if ($wilayah['abnormal_count'] < 1) {
                continue; // hanya tampilkan daerah yang melewati baku mutu
            }

            arsort($wilayah['param_abnormal']);
            $topParameters = [];
            $rank = 0;
            foreach ($wilayah['param_abnormal'] as $paramName => $count) {
                $topParameters[] = [
                    'parameter' => $paramName,
                    'abnormal_count' => $count,
                ];
                $rank++;
                if ($rank >= 5) {
                    break;
                }
            }

            $topParameter = $topParameters[0]['parameter'] ?? '-';
            $topParameterCount = $topParameters[0]['abnormal_count'] ?? 0;
            $totalSamples = count($wilayah['pasien_ids']);
            $totalPengujian = count($wilayah['pengujian_ids']);
            $pasienMelewati = count($wilayah['pasien_abnormal_ids']);
            $abnormalPct = $wilayah['total_results'] > 0
                ? round(($wilayah['abnormal_count'] / $wilayah['total_results']) * 100, 2)
                : 0;

            $coordinates = $this->getWilayahCoordinates($wilayah['nama'], $wilayah['kode'], $wilayah['tipe']);

            $mapData[] = [
                'id' => $wilayah['id'],
                'nama' => $wilayah['nama'],
                'kode' => $wilayah['kode'],
                'tipe' => $wilayah['tipe'],
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng'],
                'abnormal_count' => $wilayah['abnormal_count'],
                'below_count' => $wilayah['below_count'],
                'above_count' => $wilayah['above_count'],
                'normal_count' => $wilayah['normal_count'],
                'abnormal_percentage' => $abnormalPct,
                'total_samples' => $totalSamples,
                'total_pengujian' => $totalPengujian,
                'pasien_melewati_baku_mutu' => $pasienMelewati,
                'total_results' => $wilayah['total_results'],
                'top_parameter' => $topParameter,
                'top_parameter_count' => $topParameterCount,
                'top_parameters' => $topParameters,
                'avg_hasil' => $wilayah['abnormal_count'],
                'max_hasil' => $wilayah['abnormal_count'],
                'min_hasil' => 0,
            ];
        }

        usort($mapData, function ($a, $b) {
            return $b['abnormal_count'] <=> $a['abnormal_count'];
        });

        // Gabungkan titik yang jatuh di koordinat yang sama / berhimpit
        return $this->mergeColocatedMapPoints($mapData);
    }

    /**
     * Gabungkan marker yang berada di titik yang sama (atau hampir sama).
     */
    private function mergeColocatedMapPoints(array $mapData)
    {
        $groups = [];
        foreach ($mapData as $point) {
            // ~11m precision — titik berhimpit digabung
            $key = round((float) $point['lat'], 5) . ':' . round((float) $point['lng'], 5);
            if (!isset($groups[$key])) {
                $groups[$key] = $point;
                $groups[$key]['pasien_melewati_baku_mutu'] = (int) ($point['pasien_melewati_baku_mutu'] ?? 0);
                $groups[$key]['total_pengujian'] = (int) ($point['total_pengujian'] ?? $point['total_samples'] ?? 0);
                $groups[$key]['below_count'] = (int) ($point['below_count'] ?? 0);
                $groups[$key]['above_count'] = (int) ($point['above_count'] ?? 0);
                $groups[$key]['normal_count'] = (int) ($point['normal_count'] ?? 0);
                $groups[$key]['wilayah_list'] = [[
                    'nama' => $point['nama'],
                    'kode' => $point['kode'],
                    'tipe' => $point['tipe'],
                    'abnormal_count' => $point['abnormal_count'],
                    'pasien_melewati_baku_mutu' => (int) ($point['pasien_melewati_baku_mutu'] ?? 0),
                ]];
                $groups[$key]['merged_param_map'] = [];
                foreach ($point['top_parameters'] as $tp) {
                    $groups[$key]['merged_param_map'][$tp['parameter']] = $tp['abnormal_count'];
                }
                continue;
            }

            $groups[$key]['abnormal_count'] += $point['abnormal_count'];
            $groups[$key]['below_count'] = (int) ($groups[$key]['below_count'] ?? 0) + (int) ($point['below_count'] ?? 0);
            $groups[$key]['above_count'] = (int) ($groups[$key]['above_count'] ?? 0) + (int) ($point['above_count'] ?? 0);
            $groups[$key]['normal_count'] = (int) ($groups[$key]['normal_count'] ?? 0) + (int) ($point['normal_count'] ?? 0);
            $groups[$key]['total_samples'] += $point['total_samples'];
            $groups[$key]['total_pengujian'] = (int) ($groups[$key]['total_pengujian'] ?? 0)
                + (int) ($point['total_pengujian'] ?? $point['total_samples'] ?? 0);
            $groups[$key]['total_results'] += $point['total_results'];
            $groups[$key]['pasien_melewati_baku_mutu'] = (int) ($groups[$key]['pasien_melewati_baku_mutu'] ?? 0)
                + (int) ($point['pasien_melewati_baku_mutu'] ?? 0);
            $groups[$key]['wilayah_list'][] = [
                'nama' => $point['nama'],
                'kode' => $point['kode'],
                'tipe' => $point['tipe'],
                'abnormal_count' => $point['abnormal_count'],
                'pasien_melewati_baku_mutu' => (int) ($point['pasien_melewati_baku_mutu'] ?? 0),
            ];

            foreach ($point['top_parameters'] as $tp) {
                $name = $tp['parameter'];
                if (!isset($groups[$key]['merged_param_map'][$name])) {
                    $groups[$key]['merged_param_map'][$name] = 0;
                }
                $groups[$key]['merged_param_map'][$name] += $tp['abnormal_count'];
            }
        }

        $merged = [];
        foreach ($groups as $group) {
            $paramMap = $group['merged_param_map'] ?? [];
            arsort($paramMap);
            $topParameters = [];
            $i = 0;
            foreach ($paramMap as $paramName => $count) {
                $topParameters[] = [
                    'parameter' => $paramName,
                    'abnormal_count' => $count,
                ];
                if (++$i >= 5) {
                    break;
                }
            }

            $wilayahCount = count($group['wilayah_list']);
            $isMerged = $wilayahCount > 1;
            $nama = $isMerged
                ? ($wilayahCount . ' wilayah digabung')
                : $group['nama'];
            $kode = $isMerged ? 'multi' : $group['kode'];
            $tipe = $isMerged ? 'CLUSTER' : $group['tipe'];

            $abnormalPct = $group['total_results'] > 0
                ? round(($group['abnormal_count'] / $group['total_results']) * 100, 2)
                : 0;

            $merged[] = [
                'id' => $isMerged ? ('cluster-' . md5($group['lat'] . ':' . $group['lng'])) : $group['id'],
                'nama' => $nama,
                'kode' => $kode,
                'tipe' => $tipe,
                'lat' => $group['lat'],
                'lng' => $group['lng'],
                'abnormal_count' => $group['abnormal_count'],
                'below_count' => (int) ($group['below_count'] ?? 0),
                'above_count' => (int) ($group['above_count'] ?? 0),
                'normal_count' => (int) ($group['normal_count'] ?? 0),
                'abnormal_percentage' => $abnormalPct,
                'total_samples' => $group['total_samples'],
                'total_pengujian' => (int) ($group['total_pengujian'] ?? $group['total_samples'] ?? 0),
                'pasien_melewati_baku_mutu' => (int) ($group['pasien_melewati_baku_mutu'] ?? 0),
                'total_results' => $group['total_results'],
                'top_parameter' => $topParameters[0]['parameter'] ?? '-',
                'top_parameter_count' => $topParameters[0]['abnormal_count'] ?? 0,
                'top_parameters' => $topParameters,
                'wilayah_list' => $group['wilayah_list'],
                'is_merged' => $isMerged,
                'avg_hasil' => $group['abnormal_count'],
                'max_hasil' => $group['abnormal_count'],
                'min_hasil' => 0,
            ];
        }

        usort($merged, function ($a, $b) {
            return $b['abnormal_count'] <=> $a['abnormal_count'];
        });

        return $merged;
    }

    /**
     * Get data untuk scatter plot
     */
    private function getScatterData(array $latestPermohonanIds, $parameterIds, $tipeParameter)
    {
        if (empty($latestPermohonanIds)) {
            return [
                'data' => [],
                'labels' => [],
            ];
        }

        $query = PermohonanUjiParameterKlinik::query()
            ->join('tb_permohonan_uji_klinik_2', 'tb_permohonan_uji_parameter_klinik.permohonan_uji_klinik', '=', 'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik')
            ->join('ms_pasien', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik', '=', 'ms_pasien.id_pasien')
            ->join('ms_parameter_satuan_klinik', 'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', '=', 'ms_parameter_satuan_klinik.id_parameter_satuan_klinik')
            ->whereIn('tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik', $latestPermohonanIds)
            ->whereNotNull('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik')
            ->where('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik', '!=', '-')
            ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at')
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at');

        $query = $this->applyParameterFilter($query, $parameterIds ?: [], $tipeParameter);
        $query = $this->applyPasienDemografiFilter($query);

        $results = $query->select(array_merge([
            'tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik',
            'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
            'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
            'tb_permohonan_uji_parameter_klinik.jenis_parameter_klinik_id',
            'tb_permohonan_uji_parameter_klinik.baku_mutu_permohonan_uji_parameter_klinik',
            'tb_permohonan_uji_klinik_2.is_haji',
            DB::raw('TIMESTAMPDIFF(YEAR, ms_pasien.tgllahir_pasien, COALESCE(tb_permohonan_uji_klinik_2.tglpengujian_permohonan_uji_klinik, tb_permohonan_uji_klinik_2.created_at)) as umur_tahun'),
        ], $this->bakuMutuSelectExtras()))->get();

        $parameterGroups = [];
        foreach ($results as $result) {
            $format = $this->getParameterNumberFormat($result->parameter_satuan_klinik);
            if ($this->parseNumericValue($result->hasil_permohonan_uji_parameter_klinik, $format) === null) {
                continue;
            }
            $parameterName = $result->name_parameter_satuan_klinik;
            if (!isset($parameterGroups[$parameterName])) {
                $parameterGroups[$parameterName] = [
                    'abnormal_count' => 0,
                    'below_count' => 0,
                    'above_count' => 0,
                    'normal_count' => 0,
                ];
            }

            // Sama seperti statistik: snapshot / BM tersimpan, fallback gender+umur+haji
            $status = $this->classifyVsBakuMutu($result);
            if ($status === 'below') {
                $parameterGroups[$parameterName]['below_count']++;
                $parameterGroups[$parameterName]['abnormal_count']++;
            } elseif ($status === 'above') {
                $parameterGroups[$parameterName]['above_count']++;
                $parameterGroups[$parameterName]['abnormal_count']++;
            } elseif ($status === 'abnormal') {
                $parameterGroups[$parameterName]['abnormal_count']++;
            } else {
                $parameterGroups[$parameterName]['normal_count']++;
            }
        }

        uasort($parameterGroups, function ($a, $b) {
            return $b['abnormal_count'] <=> $a['abnormal_count'];
        });

        $scatterData = [];
        $parameterNames = [];
        $index = 0;
        foreach ($parameterGroups as $parameterName => $counts) {
            if ($counts['abnormal_count'] > 0) {
                $parameterNames[] = $parameterName;
                $scatterData[] = [
                    'x' => $index,
                    'y' => $counts['abnormal_count'],
                    'parameter' => $parameterName,
                    'abnormal_count' => $counts['abnormal_count'],
                    'below_count' => $counts['below_count'],
                    'above_count' => $counts['above_count'],
                    'normal_count' => $counts['normal_count'],
                ];
                $index++;
            }
        }

        return [
            'data' => $scatterData,
            'labels' => $parameterNames,
        ];
    }

    /**
     * Koordinat wilayah: centroid resmi Magelang (kecamatan/desa),
     * fallback induk kode, lalu lookup nama. Tanpa Nominatim live.
     */
    private function getWilayahCoordinates($wilayahName, $wilayahKode, $tipe)
    {
        $cacheKey = 'dokter_dash_geo_v2:' . $wilayahKode . ':' . $tipe;
        if (isset($this->geocodeRuntimeCache[$cacheKey])) {
            return $this->geocodeRuntimeCache[$cacheKey];
        }

        $coords = Cache::remember($cacheKey, 60 * 60 * 24 * 30, function () use ($wilayahName, $wilayahKode, $tipe) {
            $found = $this->lookupWilayahCoordinates($wilayahName, $wilayahKode, $tipe);
            if ($found) {
                return $found;
            }

            // Fallback terakhir: sebaran kecil di sekitar pusat Magelang (hindari tumpuk exact)
            $defaultLat = -7.4797;
            $defaultLng = 110.2177;
            $codeNum = abs(crc32((string) $wilayahKode));
            $latOffset = (($codeNum % 1000) / 1000) * 0.08 - 0.04;
            $lngOffset = (((int) floor($codeNum / 1000) % 1000) / 1000) * 0.08 - 0.04;

            return [
                'lat' => $defaultLat + $latOffset,
                'lng' => $defaultLng + $lngOffset,
            ];
        });

        $this->geocodeRuntimeCache[$cacheKey] = $coords;
        return $coords;
    }

    /**
     * Lookup koordinat dari dataset lokal Magelang.
     */
    private function lookupWilayahCoordinates($wilayahName, $wilayahKode, $tipe)
    {
        $index = $this->loadWilayahCoordsIndex();
        $byCode = $index['by_code'] ?? [];
        $byName = $index['by_name'] ?? [];
        $kode = preg_replace('/\D+/', '', (string) $wilayahKode);

        // Exact + parent chain: DUSUN(13) → DESA(10) → KEC(7) → KAB(4)
        $candidates = [];
        if ($kode !== '') {
            $candidates[] = $kode;
            if (strlen($kode) >= 13) {
                $candidates[] = substr($kode, 0, 10);
            }
            if (strlen($kode) >= 10) {
                $candidates[] = substr($kode, 0, 7);
            }
            if (strlen($kode) >= 7) {
                $candidates[] = substr($kode, 0, 4);
            }
        }

        foreach (array_unique($candidates) as $code) {
            if (isset($byCode[$code]) && is_array($byCode[$code]) && count($byCode[$code]) >= 2) {
                return [
                    'lat' => (float) $byCode[$code][0],
                    'lng' => (float) $byCode[$code][1],
                ];
            }
        }

        // Lookup nama (untuk kode yang beda versi BPS)
        $nameKey = $this->normalizeWilayahNameForGeo($wilayahName);
        if ($nameKey !== '') {
            $tipeKey = strtoupper((string) $tipe);
            $suffixes = [];
            if (in_array($tipeKey, ['KEC', 'KAB'], true)) {
                $suffixes[] = 'kec';
            } elseif (in_array($tipeKey, ['DESA', 'DUSUN', 'KEL'], true)) {
                $suffixes[] = 'desa';
                $suffixes[] = 'kec';
            } else {
                $suffixes = ['desa', 'kec'];
            }
            foreach ($suffixes as $suffix) {
                $key = $nameKey . '|' . $suffix;
                if (isset($byName[$key], $byCode[$byName[$key]])) {
                    $pair = $byCode[$byName[$key]];
                    return [
                        'lat' => (float) $pair[0],
                        'lng' => (float) $pair[1],
                    ];
                }
            }
        }

        return null;
    }

    private function loadWilayahCoordsIndex()
    {
        if ($this->wilayahCoordsIndex !== null) {
            return $this->wilayahCoordsIndex;
        }

        $paths = [
            public_path('assets/admin/data/wilayah_coords_magelang.json'),
            base_path('package/masterweb/src/public/assets/admin/data/wilayah_coords_magelang.json'),
        ];

        foreach ($paths as $path) {
            if (is_readable($path)) {
                $decoded = json_decode(file_get_contents($path), true);
                if (is_array($decoded)) {
                    // backward compat: flat map code => [lat,lng]
                    if (!isset($decoded['by_code']) && !isset($decoded['by_name'])) {
                        $decoded = ['by_code' => $decoded, 'by_name' => []];
                    }
                    $this->wilayahCoordsIndex = $decoded;
                    return $this->wilayahCoordsIndex;
                }
            }
        }

        $this->wilayahCoordsIndex = ['by_code' => [], 'by_name' => []];
        return $this->wilayahCoordsIndex;
    }

    private function normalizeWilayahNameForGeo($name)
    {
        $s = mb_strtolower(trim((string) $name), 'UTF-8');
        foreach (['kelurahan ', 'desa ', 'kecamatan ', 'kec. ', 'kel. ', 'dusun '] as $prefix) {
            if (strpos($s, $prefix) === 0) {
                $s = substr($s, strlen($prefix));
            }
        }
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }

    /**
     * API endpoint untuk mendapatkan wilayah options berdasarkan tipe
     */
    public function apiGetWilayahOptions(Request $request)
    {
        $tipeWilayah = $request->get('tipe_wilayah', 'KEC');
        $wilayahOptions = $this->getWilayahOptions($tipeWilayah);

        return response()->json([
            'success' => true,
            'data' => $wilayahOptions->map(function ($wilayah) {
                return [
                    'id' => $wilayah->id_wilayah,
                    'nama' => $wilayah->wilayah,
                    'kode' => $wilayah->wilayah_kode,
                ];
            }),
        ]);
    }
}
