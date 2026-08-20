<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\Wilayah;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Carbon\Carbon;

class DokterDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display dashboard dokter dengan peta persebaran hasil klinik
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
            $parameterIds = array_values($parameterIds); // Re-index array
        }
        $tipeParameter = $request->get('tipe_parameter', 'satuan'); // 'satuan' or 'paket'
        $viewType = $request->get('view_type', 'both'); // 'map', 'scatter', or 'both'
        
        // Get data wilayah berdasarkan filter
        $wilayahOptions = $this->getWilayahOptions($tipeWilayah);
        
        // Get data parameter untuk popup
        $parameterSatuans = ParameterSatuanKlinik::whereNull('deleted_at')
            ->orderBy('name_parameter_satuan_klinik', 'ASC')
            ->get();
        $parameterPakets = ParameterPaketKlinik::whereNull('deleted_at')
            ->orderBy('name_parameter_paket_klinik', 'ASC')
            ->get();
        
        // Get data statistik
        $statistics = $this->getStatistics($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter);
        
        // Get data untuk peta
        $mapData = $this->getMapData($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter);
        
        // Get data untuk scatter plot
        $scatterData = $this->getScatterData($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter);
        
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
            'viewType'
        ));
    }

    /**
     * Get wilayah options berdasarkan tipe
     */
    private function getWilayahOptions($tipeWilayah)
    {
        if ($tipeWilayah === 'luar_daerah') {
            // Luar daerah Kabupaten Magelang (selain 3308)
            return Wilayah::where('tipe', 'KAB')
                ->where('wilayah_kode', 'NOT LIKE', '3308%')
                ->orderBy('wilayah', 'ASC')
                ->get();
        } else {
            // Dalam daerah Kabupaten Magelang (3308)
            if ($tipeWilayah === 'DESA') {
                return Wilayah::where('tipe', 'DESA')
                    ->where('wilayah_kode', 'LIKE', '3308%')
                    ->orderBy('wilayah', 'ASC')
                    ->get();
            } elseif ($tipeWilayah === 'DUSUN') {
                // Jika ada tipe DUSUN, sesuaikan dengan struktur database
                return Wilayah::where('tipe', 'DUSUN')
                    ->where('wilayah_kode', 'LIKE', '3308%')
                    ->orderBy('wilayah', 'ASC')
                    ->get();
            } else {
                // Default KEC
                return Wilayah::where('tipe', 'KEC')
                    ->where('wilayah_kode', 'LIKE', '3308%')
                    ->orderBy('wilayah', 'ASC')
                    ->get();
            }
        }
    }

    /**
     * Get latest permohonan per pasien based on filters
     */
    private function getLatestPermohonanPerPasien($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter)
    {
        // First, get all pasien IDs that match wilayah filter
        $pasienQuery = Pasien::query()
            ->join('ms_wilayah', 'ms_pasien.wilayah_id', '=', 'ms_wilayah.id_wilayah');
        
        // Apply wilayah filter
        if ($tipeWilayah === 'luar_daerah') {
            $pasienQuery->where('ms_wilayah.wilayah_kode', 'NOT LIKE', '3308%');
        } else {
            $pasienQuery->where('ms_wilayah.wilayah_kode', 'LIKE', '3308%');
            
            if ($wilayahId) {
                $wilayah = Wilayah::find($wilayahId);
                if ($wilayah) {
                    if ($tipeWilayah === 'DESA' || $tipeWilayah === 'DUSUN') {
                        $pasienQuery->where('ms_pasien.wilayah_id', $wilayahId);
                    } elseif ($tipeWilayah === 'KEC') {
                        $desaIds = Wilayah::where('tipe', 'DESA')
                            ->where('wilayah_kode', 'LIKE', $wilayah->wilayah_kode . '%')
                            ->pluck('id_wilayah');
                        $pasienQuery->where(function($q) use ($desaIds, $wilayahId) {
                            $q->whereIn('ms_pasien.wilayah_id', $desaIds)
                            ->orWhere('ms_pasien.wilayah_id', $wilayahId);
                        });
                    }
                }
            }
        }
        
        $pasienIds = $pasienQuery->pluck('ms_pasien.id_pasien')->toArray();
        
        if (empty($pasienIds)) {
            return [];
        }
        
        // Get latest permohonan per pasien
        // For each pasien, get the latest permohonan based on tglpengujian or created_at
        $permohonanIds = [];
        
        foreach ($pasienIds as $pasienId) {
            $permohonanQuery = PermohonanUjiKlinik2::where('pasien_permohonan_uji_klinik', $pasienId)
                ->whereNull('deleted_at');
            
            // Filter bulan dan tahun
            if ($bulan && $tahun) {
                $permohonanQuery->where(function($q) use ($bulan, $tahun) {
                    $q->where(function($subQ) use ($bulan, $tahun) {
                        $subQ->whereNotNull('tglpengujian_permohonan_uji_klinik')
                            ->whereYear('tglpengujian_permohonan_uji_klinik', $tahun)
                            ->whereMonth('tglpengujian_permohonan_uji_klinik', $bulan);
                    })->orWhere(function($subQ) use ($bulan, $tahun) {
                        $subQ->whereNull('tglpengujian_permohonan_uji_klinik')
                            ->whereYear('created_at', $tahun)
                            ->whereMonth('created_at', $bulan);
                    });
                });
            }
            
            // Get the latest permohonan for this pasien
            $latestPermohonan = $permohonanQuery
                ->orderByRaw('COALESCE(tglpengujian_permohonan_uji_klinik, created_at) DESC')
                ->orderBy('created_at', 'DESC')
                ->first();
            
            if ($latestPermohonan) {
                $permohonanIds[] = $latestPermohonan->id_permohonan_uji_klinik;
            }
        }
        
        return $permohonanIds;
    }

    /**
     * Get statistics data - per pasien, menggunakan hasil terakhir
     */
    private function getStatistics($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter)
    {
        // Get latest permohonan IDs per pasien
        $latestPermohonanIds = $this->getLatestPermohonanPerPasien($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter);
        
        if (empty($latestPermohonanIds)) {
            return [
                'total_samples' => 0,
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
            ->join('ms_wilayah', 'ms_pasien.wilayah_id', '=', 'ms_wilayah.id_wilayah')
            ->leftJoin('ms_parameter_satuan_klinik', 'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', '=', 'ms_parameter_satuan_klinik.id_parameter_satuan_klinik')
            ->whereIn('tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik', $latestPermohonanIds)
            ->whereNotNull('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik')
            ->where('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik', '!=', '-')
            ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at')
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at');
        
        // Filter parameter
        if (!empty($parameterIds) && count($parameterIds) > 0) {
            $filterIds = array_map(function($id) {
                return is_numeric($id) ? (int)$id : $id;
            }, $parameterIds);
            
            if ($tipeParameter === 'paket') {
                $paketJenisIds = ParameterPaketJenisKlinik::whereIn('parameter_paket_klinik_id', $filterIds)
                    ->whereNull('deleted_at')
                    ->pluck('id_parameter_paket_jenis_klinik')
                    ->toArray();
                
                $parameterSatuanIds = ParameterSatuanPaketKlinik::whereIn('parameter_paket_jenis_klinik', $paketJenisIds)
                    ->whereNull('deleted_at')
                    ->pluck('parameter_satuan_klinik')
                    ->unique()
                    ->toArray();
                
                if (!empty($parameterSatuanIds)) {
                    $query->whereIn('tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', $parameterSatuanIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', $filterIds);
            }
        }

        $results = $query->select(
            'tb_permohonan_uji_parameter_klinik.*',
            'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik',
            'ms_wilayah.wilayah_kode',
            'ms_wilayah.wilayah',
            'ms_wilayah.tipe as wilayah_tipe',
            'ms_pasien.wilayah_id',
            'ms_parameter_satuan_klinik.name_parameter_satuan_klinik'
        )->get();

        // Calculate statistics
        // Total samples = distinct pasien (karena sudah diambil hasil terakhir per pasien)
        $uniquePasien = $results->unique('pasien_permohonan_uji_klinik');
        $totalSamples = $uniquePasien->count();
        
        // Group hasil per parameter untuk menghitung rata-rata dan maksimal per parameter
        $parameterResults = [];
        $parameterAbnormalCounts = [];
        $allResultsNumeric = [];
        
        foreach ($results as $result) {
            $hasil = $result->hasil_permohonan_uji_parameter_klinik;
            $parameterId = $result->parameter_satuan_klinik;
            
            if (is_numeric($hasil)) {
                $hasilNumeric = (float)$hasil;
                $allResultsNumeric[] = $hasilNumeric;
                
                // Group by parameter
                if (!isset($parameterResults[$parameterId])) {
                    $parameterResults[$parameterId] = [];
                    $parameterAbnormalCounts[$parameterId] = 0;
                }
                $parameterResults[$parameterId][] = $hasilNumeric;
                
                // Check if abnormal (melewati baku mutu)
                if ($this->isAbnormal($result)) {
                    $parameterAbnormalCounts[$parameterId]++;
                }
            }
        }
        
        // Hitung statistik per parameter
        $parameterStats = [];
        $totalAbnormalCount = 0;
        
        // Get parameter names from results
        $parameterNames = [];
        foreach ($results as $result) {
            $parameterId = $result->parameter_satuan_klinik;
            if (!isset($parameterNames[$parameterId])) {
                $parameterNames[$parameterId] = $result->name_parameter_satuan_klinik ?? 'Parameter #' . $parameterId;
            }
        }
        
        foreach ($parameterResults as $parameterId => $values) {
            if (count($values) > 0) {
                $parameterName = $parameterNames[$parameterId] ?? 'Parameter #' . $parameterId;
                
                $avg = array_sum($values) / count($values);
                $max = max($values);
                $min = min($values);
                $abnormalCount = $parameterAbnormalCounts[$parameterId] ?? 0;
                $abnormalPercentage = count($values) > 0 ? ($abnormalCount / count($values)) * 100 : 0;
                
                $parameterStats[] = [
                    'parameter_id' => $parameterId,
                    'parameter_name' => $parameterName,
                    'average' => round($avg, 2),
                    'max' => round($max, 2),
                    'min' => round($min, 2),
                    'abnormal_count' => $abnormalCount,
                    'abnormal_percentage' => round($abnormalPercentage, 2),
                    'total_results' => count($values)
                ];
                
                $totalAbnormalCount += $abnormalCount;
            }
        }
        
        // Sort by parameter name
        usort($parameterStats, function($a, $b) {
            return strcmp($a['parameter_name'], $b['parameter_name']);
        });
        
        // Calculate overall statistics
        $overallAverage = count($parameterStats) > 0 ? array_sum(array_column($parameterStats, 'average')) / count($parameterStats) : 0;
        $overallMax = count($parameterStats) > 0 ? max(array_column($parameterStats, 'max')) : 0;
        $overallMin = count($parameterStats) > 0 ? min(array_column($parameterStats, 'min')) : 0;
        $overallAbnormalPercentage = count($allResultsNumeric) > 0 ? ($totalAbnormalCount / count($allResultsNumeric)) * 100 : 0;

        return [
            'total_samples' => $totalSamples,
            'average' => round($overallAverage, 2),
            'max' => round($overallMax, 2),
            'min' => round($overallMin, 2),
            'abnormal_count' => $totalAbnormalCount,
            'abnormal_percentage' => round($overallAbnormalPercentage, 2),
            'parameter_stats' => $parameterStats, // Data per parameter
        ];
    }

    /**
     * Check if result is abnormal (melewati baku mutu)
     */
    private function isAbnormal($parameterKlinik)
    {
        $parameterSatuanKlinikId = is_object($parameterKlinik) ? $parameterKlinik->parameter_satuan_klinik : $parameterKlinik['parameter_satuan_klinik'] ?? null;
        $jenisParameterKlinikId = is_object($parameterKlinik) ? $parameterKlinik->jenis_parameter_klinik_id : $parameterKlinik['jenis_parameter_klinik_id'] ?? null;
        $hasil = is_object($parameterKlinik) ? $parameterKlinik->hasil_permohonan_uji_parameter_klinik : $parameterKlinik['hasil_permohonan_uji_parameter_klinik'] ?? null;
        
        if (!$parameterSatuanKlinikId || !$jenisParameterKlinikId || !$hasil) {
            return false;
        }
        
        // Get baku mutu
        $bakuMutu = BakuMutu::where('parameter_satuan_klinik_id', $parameterSatuanKlinikId)
            ->where('parameter_jenis_klinik_id', $jenisParameterKlinikId)
            ->first();

        if (!$bakuMutu) {
            return false;
        }

        if (!is_numeric($hasil)) {
            return false;
        }

        $hasilNumeric = (float)$hasil;

        // Check equal
        if ($bakuMutu->equal !== null && $bakuMutu->equal !== '' && $bakuMutu->equal != '0') {
            if ($hasilNumeric != (float)$bakuMutu->equal) {
                return true;
            }
        }

        // Check min and max
        if ($bakuMutu->min !== null && $bakuMutu->max !== null) {
            if ($hasilNumeric < (float)$bakuMutu->min || $hasilNumeric > (float)$bakuMutu->max) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get data untuk peta Leaflet - per pasien, menggunakan hasil terakhir
     */
    private function getMapData($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter)
    {
        // Get latest permohonan IDs per pasien
        $latestPermohonanIds = $this->getLatestPermohonanPerPasien($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter);
        
        if (empty($latestPermohonanIds)) {
            return [];
        }
        
        $query = PermohonanUjiParameterKlinik::query()
            ->join('tb_permohonan_uji_klinik_2', 'tb_permohonan_uji_parameter_klinik.permohonan_uji_klinik', '=', 'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik')
            ->join('ms_pasien', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik', '=', 'ms_pasien.id_pasien')
            ->join('ms_wilayah', 'ms_pasien.wilayah_id', '=', 'ms_wilayah.id_wilayah')
            ->whereIn('tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik', $latestPermohonanIds)
            ->whereNotNull('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik')
            ->where('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik', '!=', '-')
            ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at')
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at');
        
        // Filter parameter
        if (!empty($parameterIds) && count($parameterIds) > 0) {
            $filterIds = array_map(function($id) {
                return is_numeric($id) ? (int)$id : $id;
            }, $parameterIds);
            
            if ($tipeParameter === 'paket') {
                $paketJenisIds = ParameterPaketJenisKlinik::whereIn('parameter_paket_klinik_id', $filterIds)
                    ->whereNull('deleted_at')
                    ->pluck('id_parameter_paket_jenis_klinik')
                    ->toArray();
                
                $parameterSatuanIds = ParameterSatuanPaketKlinik::whereIn('parameter_paket_jenis_klinik', $paketJenisIds)
                    ->whereNull('deleted_at')
                    ->pluck('parameter_satuan_klinik')
                    ->unique()
                    ->toArray();
                
                if (!empty($parameterSatuanIds)) {
                    $query->whereIn('tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', $parameterSatuanIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', $filterIds);
            }
        }

        $results = $query->select(
            'ms_wilayah.id_wilayah',
            'ms_wilayah.wilayah',
            'ms_wilayah.wilayah_kode',
            'ms_wilayah.tipe as wilayah_tipe',
            DB::raw('AVG(CAST(tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik AS DECIMAL(10,2))) as avg_hasil'),
            DB::raw('MAX(CAST(tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik AS DECIMAL(10,2))) as max_hasil'),
            DB::raw('MIN(CAST(tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik AS DECIMAL(10,2))) as min_hasil'),
            DB::raw('COUNT(DISTINCT tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik) as total_samples')
        )
        ->groupBy('ms_wilayah.id_wilayah', 'ms_wilayah.wilayah', 'ms_wilayah.wilayah_kode', 'ms_wilayah.tipe')
        ->get();

        $mapData = [];
        foreach ($results as $result) {
            // Get coordinates using geocoding
            $coordinates = $this->getWilayahCoordinates($result->wilayah, $result->wilayah_kode, $result->wilayah_tipe);
            
            $mapData[] = [
                'id' => $result->id_wilayah,
                'nama' => $result->wilayah,
                'kode' => $result->wilayah_kode,
                'tipe' => $result->wilayah_tipe,
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng'],
                'avg_hasil' => round($result->avg_hasil, 2),
                'max_hasil' => round($result->max_hasil, 2),
                'min_hasil' => round($result->min_hasil, 2),
                'total_samples' => $result->total_samples,
            ];
        }

        return $mapData;
    }

    /**
     * Get data untuk scatter plot - per pasien, menggunakan hasil terakhir
     */
    private function getScatterData($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter)
    {
        // Get latest permohonan IDs per pasien
        $latestPermohonanIds = $this->getLatestPermohonanPerPasien($tipeWilayah, $wilayahId, $bulan, $tahun, $parameterIds, $tipeParameter);
        
        if (empty($latestPermohonanIds)) {
            return [
                'data' => [],
                'labels' => []
            ];
        }
        
        $query = PermohonanUjiParameterKlinik::query()
            ->join('tb_permohonan_uji_klinik_2', 'tb_permohonan_uji_parameter_klinik.permohonan_uji_klinik', '=', 'tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik')
            ->join('ms_pasien', 'tb_permohonan_uji_klinik_2.pasien_permohonan_uji_klinik', '=', 'ms_pasien.id_pasien')
            ->join('ms_wilayah', 'ms_pasien.wilayah_id', '=', 'ms_wilayah.id_wilayah')
            ->join('ms_parameter_satuan_klinik', 'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', '=', 'ms_parameter_satuan_klinik.id_parameter_satuan_klinik')
            ->whereIn('tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik', $latestPermohonanIds)
            ->whereNotNull('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik')
            ->where('tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik', '!=', '-')
            ->whereNull('tb_permohonan_uji_parameter_klinik.deleted_at')
            ->whereNull('tb_permohonan_uji_klinik_2.deleted_at');
        
        // Filter parameter
        if (!empty($parameterIds) && count($parameterIds) > 0) {
            $filterIds = array_map(function($id) {
                return is_numeric($id) ? (int)$id : $id;
            }, $parameterIds);
            
            if ($tipeParameter === 'paket') {
                $paketJenisIds = ParameterPaketJenisKlinik::whereIn('parameter_paket_klinik_id', $filterIds)
                    ->whereNull('deleted_at')
                    ->pluck('id_parameter_paket_jenis_klinik')
                    ->toArray();
                
                $parameterSatuanIds = ParameterSatuanPaketKlinik::whereIn('parameter_paket_jenis_klinik', $paketJenisIds)
                    ->whereNull('deleted_at')
                    ->pluck('parameter_satuan_klinik')
                    ->unique()
                    ->toArray();
                
                if (!empty($parameterSatuanIds)) {
                    $query->whereIn('tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', $parameterSatuanIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik', $filterIds);
            }
        }

        $results = $query->select(
            'tb_permohonan_uji_parameter_klinik.hasil_permohonan_uji_parameter_klinik',
            'ms_parameter_satuan_klinik.name_parameter_satuan_klinik',
            'tb_permohonan_uji_parameter_klinik.parameter_satuan_klinik',
            'tb_permohonan_uji_parameter_klinik.jenis_parameter_klinik_id'
        )->get();

        // Group by parameter name and count abnormal (melewati baku mutu)
        $parameterGroups = [];
        foreach ($results as $result) {
            $hasil = $result->hasil_permohonan_uji_parameter_klinik;
            if (is_numeric($hasil)) {
                $parameterName = $result->name_parameter_satuan_klinik;
                $isAbnormal = $this->isAbnormal($result);
                
                if (!isset($parameterGroups[$parameterName])) {
                    $parameterGroups[$parameterName] = 0;
                }
                
                if ($isAbnormal) {
                    $parameterGroups[$parameterName]++;
                }
            }
        }

        // Convert to array format for chart: X = parameter name, Y = jumlah abnormal
        $scatterData = [];
        $parameterNames = [];
        $index = 0;
        foreach ($parameterGroups as $parameterName => $abnormalCount) {
            if ($abnormalCount > 0) { // Only show parameters with abnormal cases
                $parameterNames[] = $parameterName;
                
                $scatterData[] = [
                    'x' => $index,
                    'y' => $abnormalCount,
                    'parameter' => $parameterName,
                    'abnormal_count' => $abnormalCount
                ];
                
                $index++;
            }
        }

        return [
            'data' => $scatterData,
            'labels' => $parameterNames
        ];
    }

    /**
     * Get baku mutu value for scatter plot
     */
    private function getBakuMutuValue($parameterSatuanKlinikId, $jenisParameterKlinikId)
    {
        $bakuMutu = BakuMutu::where('parameter_satuan_klinik_id', $parameterSatuanKlinikId)
            ->where('parameter_jenis_klinik_id', $jenisParameterKlinikId)
            ->first();

        if ($bakuMutu) {
            if ($bakuMutu->max !== null) {
                return (float)$bakuMutu->max;
            } elseif ($bakuMutu->min !== null) {
                return (float)$bakuMutu->min;
            } elseif ($bakuMutu->equal !== null) {
                return (float)$bakuMutu->equal;
            }
        }

        return 0;
    }

    /**
     * Get coordinates for wilayah using geocoding with multiple strategies
     */
    private function getWilayahCoordinates($wilayahName, $wilayahKode, $tipe)
    {
        // Default coordinates for Magelang Regency center
        $defaultLat = -7.4706;
        $defaultLng = 110.2178;
        
        // Get parent wilayah information for more specific queries
        $kecamatan = null;
        $kabupaten = null;
        
        if ($tipe === 'DESA' || $tipe === 'DUSUN') {
            // Extract kecamatan code from wilayah_kode (first 6 digits)
            $kecCode = substr($wilayahKode, 0, 6);
            $kecamatan = Wilayah::where('wilayah_kode', $kecCode)
                ->where('tipe', 'KEC')
                ->first();
        }
        
        // Build multiple query variations for better accuracy
        $queries = [];
        
        if ($tipe === 'DESA' || $tipe === 'DUSUN') {
            if ($kecamatan) {
                // Most specific: Desa, Kecamatan, Kabupaten Magelang
                $queries[] = $wilayahName . ', Kecamatan ' . $kecamatan->wilayah . ', Kabupaten Magelang, Jawa Tengah, Indonesia';
                $queries[] = 'Desa ' . $wilayahName . ', ' . $kecamatan->wilayah . ', Magelang, Jawa Tengah';
                $queries[] = $wilayahName . ', ' . $kecamatan->wilayah . ', Magelang Regency, Central Java, Indonesia';
            }
            $queries[] = $wilayahName . ', Kabupaten Magelang, Jawa Tengah, Indonesia';
            $queries[] = 'Desa ' . $wilayahName . ', Magelang, Jawa Tengah';
        } elseif ($tipe === 'KEC') {
            $queries[] = 'Kecamatan ' . $wilayahName . ', Kabupaten Magelang, Jawa Tengah, Indonesia';
            $queries[] = $wilayahName . ', Kabupaten Magelang, Central Java, Indonesia';
            $queries[] = 'Kecamatan ' . $wilayahName . ', Magelang Regency, Indonesia';
        } else {
            $queries[] = $wilayahName . ', Kabupaten Magelang, Jawa Tengah, Indonesia';
        }
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: MagelangLabkes/1.0 (Contact: admin@magelanglabkes.go.id)',
                    'Accept-Language: id,en-US;q=0.9,en;q=0.8'
                ],
                'timeout' => 10
            ]
        ]);
        
        // Try each query variation
        foreach ($queries as $query) {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $query,
                'format' => 'json',
                'limit' => 5, // Get more results for better matching
                'countrycodes' => 'id',
                'addressdetails' => 1,
                'extratags' => 1,
                'namedetails' => 1
            ]);
            
            try {
                $response = @file_get_contents($url, false, $context);
                if ($response) {
                    $data = json_decode($response, true);
                    if (!empty($data)) {
                        $bestMatch = $this->findBestGeocodeMatch($data, $wilayahName, $kecamatan, $tipe);
                        if ($bestMatch) {
                            return [
                                'lat' => (float)$bestMatch['lat'],
                                'lng' => (float)$bestMatch['lon']
                            ];
                        }
                    }
                }
                
                // Small delay to respect rate limits
                usleep(200000); // 0.2 seconds
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Fallback: Use calculated coordinates with better distribution
        // Magelang Regency bounds: Lat: -7.2 to -7.6, Lng: 110.0 to 110.4
        $codeNum = (int)substr($wilayahKode, -4);
        $latRange = 0.4; // Total range
        $lngRange = 0.4;
        
        // Distribute based on code
        $latOffset = (($codeNum % 100) / 100) * $latRange - ($latRange / 2);
        $lngOffset = ((floor($codeNum / 100) % 100) / 100) * $lngRange - ($lngRange / 2);
        
        return [
            'lat' => $defaultLat + $latOffset,
            'lng' => $defaultLng + $lngOffset
        ];
    }
    
    /**
     * Find best geocode match from results
     */
    private function findBestGeocodeMatch($results, $wilayahName, $kecamatan = null, $tipe = null)
    {
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($results as $result) {
            $score = 0;
            $address = isset($result['display_name']) ? strtolower($result['display_name']) : '';
            $addressDetails = isset($result['address']) ? $result['address'] : [];
            
            // Must contain Magelang
            if (stripos($address, 'magelang') === false) {
                continue;
            }
            
            // Check if wilayah name matches
            $wilayahLower = strtolower($wilayahName);
            if (stripos($address, $wilayahLower) !== false) {
                $score += 50;
            }
            
            // Check if kecamatan matches (for desa)
            if ($kecamatan && $tipe === 'DESA') {
                $kecLower = strtolower($kecamatan->wilayah);
                if (stripos($address, $kecLower) !== false) {
                    $score += 30;
                }
            }
            
            // Check address type matches
            if ($tipe === 'DESA' || $tipe === 'DUSUN') {
                if (isset($addressDetails['village']) || isset($addressDetails['hamlet'])) {
                    $score += 20;
                }
            } elseif ($tipe === 'KEC') {
                if (isset($addressDetails['county']) || isset($addressDetails['municipality'])) {
                    $score += 20;
                }
            }
            
            // Add importance score
            $importance = isset($result['importance']) ? (float)$result['importance'] : 0;
            $score += $importance * 10;
            
            // Prefer results in Central Java / Jawa Tengah
            if (stripos($address, 'jawa tengah') !== false || stripos($address, 'central java') !== false) {
                $score += 10;
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $result;
            }
        }
        
        // If we found a good match (score > 50), return it
        if ($bestMatch && $bestScore > 50) {
            return $bestMatch;
        }
        
        // Otherwise return first result if available
        return !empty($results[0]) ? $results[0] : null;
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
            'data' => $wilayahOptions->map(function($wilayah) {
                return [
                    'id' => $wilayah->id_wilayah,
                    'nama' => $wilayah->wilayah,
                    'kode' => $wilayah->wilayah_kode,
                ];
            })
        ]);
    }
}