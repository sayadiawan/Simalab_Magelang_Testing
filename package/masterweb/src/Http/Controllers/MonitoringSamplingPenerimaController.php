<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\PengambilanSampleKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\KlinikNumberSettings;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringSamplingPenerimaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            
            // Get data for the selected month
            $data = $this->getMonitoringData($month, $year);
            
            return view('masterweb::module.admin.laboratorium.report.v2.klinik.monitoring-sampling-penerima', [
                'data' => $data,
                'month' => $month,
                'year' => $year
            ]);   
        } catch (\Throwable $th) {
            \Log::error('MonitoringSamplingPenerimaController Error: ' . $th->getMessage());
            \Log::error('Stack trace: ' . $th->getTraceAsString());
            
            // If no previous URL, redirect to home
            if (!url()->previous() || url()->previous() == url()->current()) {
                return redirect()->route('home')->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }
    
    public function export(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            
            // Get data for the selected month
            $data = $this->getMonitoringData($month, $year);
            
            $filename = 'Monitoring_Sampling_Penerima_' . \Smt\Masterweb\Helpers\Smt::fbulan(sprintf('%02d', $month)) . '_' . $year . '.xlsx';
            
            return Excel::download(new \Smt\Masterweb\Exports\MonitoringSamplingPenerimaExport($data, $month, $year), $filename);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
    
    private function getMonitoringData($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $klinikNumberSettings = KlinikNumberSettings::getSettings();
        
        // Get all permohonan uji klinik for the month
        $permohonanList = PermohonanUjiKlinik2::whereBetween(DB::raw('DATE(created_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNull('deleted_at')
            ->with(['pasien', 'permohonanujipaketklinik' => function($query) {
                $query->whereNull('deleted_at')->with('parameterjenisklinik');
            }])
            ->orderByNomerSpesimen('asc', $klinikNumberSettings)
            ->get();

        $permohonanIds = $permohonanList->pluck('id_permohonan_uji_klinik')->filter()->values();

        $verificationByPermohonan = VerificationActivitySample::whereIn('is_klinik', $permohonanIds)
            ->whereIn('id_verification_activity', [1, 6, 7])
            ->get()
            ->groupBy('is_klinik');

        $pengambilanByPermohonan = PengambilanSampleKlinik::whereIn('permohonan_uji_klinik_id', $permohonanIds)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('permohonan_uji_klinik_id');

        $monitoringData = [];
        
        foreach ($permohonanList as $index => $permohonan) {
            $pasien = $permohonan->pasien;

            $vaByStep = ($verificationByPermohonan->get($permohonan->id_permohonan_uji_klinik) ?? collect())
                ->keyBy('id_verification_activity');
            $vaStep1 = $vaByStep->get(1);
            $vaStep6 = $vaByStep->get(6);
            $vaStep7 = $vaByStep->get(7);

            $pengambilanSample = ($pengambilanByPermohonan->get($permohonan->id_permohonan_uji_klinik) ?? collect())->first();
            
            // Get jenis pemeriksaan
            $jenisPemeriksaan = [];
            $paketList = $permohonan->permohonanujipaketklinik;
            foreach ($paketList as $paket) {
                if ($paket->parameterjenisklinik) {
                    $jenisPemeriksaan[] = $paket->parameterjenisklinik->name_parameter_jenis_klinik;
                }
            }
            $jenisPemeriksaanStr = !empty($jenisPemeriksaan) ? implode(', ', array_unique($jenisPemeriksaan)) : '-';
            
            // Get jenis sampel
            $jenisSampel = [];
            if ($pengambilanSample && !empty($pengambilanSample->jenis_sample)) {
                if (is_string($pengambilanSample->jenis_sample)) {
                    $decoded = json_decode($pengambilanSample->jenis_sample, true);
                    if (is_array($decoded) && !empty($decoded)) {
                        $jenisSampel = $decoded;
                    } else {
                        $jenisSampel = !empty($pengambilanSample->jenis_sample) ? [$pengambilanSample->jenis_sample] : [];
                    }
                } else if (is_array($pengambilanSample->jenis_sample)) {
                    $jenisSampel = $pengambilanSample->jenis_sample;
                }
            }
            
            // If no jenis sampel from sampling, try to get from helper
            if (empty($jenisSampel)) {
                try {
                    $jenisSampel = \Smt\Masterweb\Helpers\Smt::getJenisSampelFromParameter($permohonan->id_permohonan_uji_klinik, null);
                    if (!is_array($jenisSampel)) {
                        $jenisSampel = [];
                    }
                } catch (\Exception $e) {
                    $jenisSampel = [];
                }
            }
            $jenisSampelStr = !empty($jenisSampel) ? implode(', ', $jenisSampel) : '-';
            
            // Status/jam/petugas sampling: utamakan step 6 (pengambilan sample), bukan data pendaftaran
            // Urine tidak dilakukan sampling → petugas & jam sampling dikosongkan (-)
            $isUrineOnly = $this->isUrineOnlySample($jenisSampel);
            $statusDisplay = $this->resolveSamplingStatusDisplay($vaStep6, $pengambilanSample);
            if ($isUrineOnly) {
                $jamSampling = '-';
                $petugasSampling = '-';
            } else {
                $jamSampling = $this->resolveSamplingJam($vaStep6, $pengambilanSample);
                $petugasSampling = $this->resolveSamplingPetugas($vaStep6, $vaStep1, $pengambilanSample, $permohonan);
            }
            
            // No spesimen mengikuti kolom nomer pemeriksaan di halaman registrasi
            $noSpesimen = $permohonan->getDisplayNoregister($klinikNumberSettings);
            
            // Parse volume and kualitas sampel (JSON format)
            $volumeSampelData = [];
            $kualitasSampelData = [];
            
            if ($permohonan->volume_sampel) {
                if (is_string($permohonan->volume_sampel)) {
                    $decoded = json_decode($permohonan->volume_sampel, true);
                    $volumeSampelData = is_array($decoded) ? $decoded : [];
                } else if (is_array($permohonan->volume_sampel)) {
                    $volumeSampelData = $permohonan->volume_sampel;
                }
            }
            
            if ($permohonan->kualitas_sampel) {
                if (is_string($permohonan->kualitas_sampel)) {
                    $decoded = json_decode($permohonan->kualitas_sampel, true);
                    $kualitasSampelData = is_array($decoded) ? $decoded : [];
                } else if (is_array($permohonan->kualitas_sampel)) {
                    $kualitasSampelData = $permohonan->kualitas_sampel;
                }
            }
            
            // Penerimaan sampel dari step 7
            $petugasPenerimaan = '-';
            $jamPenerimaan = '-';
            $keterangan = '-';

            if ($vaStep7) {
                $petugasPenerimaan = $vaStep7->nama_petugas ?? '-';
                $jamPenerimaan = $vaStep7->start_date
                    ? Carbon::parse($vaStep7->start_date)->format('H.i')
                    : '-';
            }
            
            // Helper function to convert value to string
            $toString = function($value) {
                if (is_array($value)) {
                    return implode(', ', $value);
                }
                return (string)($value ?? '-');
            };
            
            $monitoringData[] = [
                'no' => $index + 1,
                'tanggal' => Carbon::parse($permohonan->created_at)->format('d/m/Y'),
                'no_rm' => $pasien->no_rekammedis_pasien ?? '-',
                'no_spesimen' => $noSpesimen,
                'nama_pasien' => $pasien->nama_pasien ?? '-',
                'jenis_pemeriksaan' => $jenisPemeriksaanStr,
                'jenis_sampel' => $jenisSampelStr,
                'status_sampling' => $statusDisplay,
                'petugas_sampling' => $petugasSampling,
                'jam_sampling' => $jamSampling,
                'darah_volume' => $toString($volumeSampelData['Darah'] ?? $volumeSampelData['darah'] ?? null),
                'darah_kualitas' => $toString($kualitasSampelData['Darah'] ?? $kualitasSampelData['darah'] ?? null),
                'serum_volume' => $toString($volumeSampelData['Serum'] ?? $volumeSampelData['serum'] ?? null),
                'serum_kualitas' => $toString($kualitasSampelData['Serum'] ?? $kualitasSampelData['serum'] ?? null),
                'urine_volume' => $toString($volumeSampelData['Urine'] ?? $volumeSampelData['urine'] ?? null),
                'urine_kualitas' => $toString($kualitasSampelData['Urine'] ?? $kualitasSampelData['urine'] ?? null),
                'feses_volume' => $toString($volumeSampelData['Feses'] ?? $volumeSampelData['feses'] ?? null),
                'feses_kualitas' => $toString($kualitasSampelData['Feses'] ?? $kualitasSampelData['feses'] ?? null),
                'petugas_penerimaan' => $petugasPenerimaan,
                'jam_penerimaan' => $jamPenerimaan,
                'keterangan' => $keterangan
            ];
        }
        
        return $monitoringData;
    }

    /**
     * True jika jenis sampel hanya urine (tidak ada darah/serum yang perlu sampling).
     */
    private function isUrineOnlySample(array $jenisSampel): bool
    {
        if (empty($jenisSampel)) {
            return false;
        }

        $normalized = array_values(array_filter(array_map(function ($item) {
            return strtolower(trim((string) $item));
        }, $jenisSampel)));

        if (empty($normalized)) {
            return false;
        }

        foreach ($normalized as $item) {
            // Urine / urin dianggap tidak sampling
            if (strpos($item, 'urine') !== false || strpos($item, 'urin') !== false) {
                continue;
            }

            // Ada jenis sampel lain (darah, serum, dll.) → tetap sampling
            return false;
        }

        return true;
    }

    private function resolveSamplingStatusDisplay($vaStep6, $pengambilanSample): string
    {
        if ($vaStep6 && (int) ($vaStep6->is_done ?? 0) === 1) {
            return 'V';
        }

        $status = strtolower(trim((string) (optional($pengambilanSample)->status_sampling ?? '')));
        if ($status === 'berhasil') {
            return 'V';
        }
        if ($status === 'gagal') {
            return 'X';
        }

        return '-';
    }

    private function resolveSamplingJam($vaStep6, $pengambilanSample): string
    {
        if ($vaStep6 && !empty($vaStep6->start_date)) {
            return Carbon::parse($vaStep6->start_date)->format('H.i');
        }

        if ($pengambilanSample && !empty($pengambilanSample->time_sampling)) {
            return Carbon::parse($pengambilanSample->time_sampling)->format('H.i');
        }

        if ($pengambilanSample && !empty($pengambilanSample->created_at)) {
            $status = strtolower(trim((string) (optional($pengambilanSample)->status_sampling ?? '')));
            if (in_array($status, ['berhasil', 'gagal'], true)) {
                return Carbon::parse($pengambilanSample->created_at)->format('H.i');
            }
        }

        return '-';
    }

    private function resolveSamplingPetugas($vaStep6, $vaStep1, $pengambilanSample, $permohonan): string
    {
        if ($vaStep6 && !empty(trim((string) $vaStep6->nama_petugas))) {
            return trim((string) $vaStep6->nama_petugas);
        }

        if (!empty(trim((string) ($permohonan->plebotomist_permohonan_uji_klinik ?? '')))) {
            return trim((string) $permohonan->plebotomist_permohonan_uji_klinik);
        }

        if ($pengambilanSample && !empty(trim((string) ($pengambilanSample->petugas_name ?? '')))) {
            $samplePetugas = trim((string) $pengambilanSample->petugas_name);
            $regPetugas = trim((string) ($vaStep1->nama_petugas ?? ''));
            if ($regPetugas === '' || strcasecmp($samplePetugas, $regPetugas) !== 0) {
                return $samplePetugas;
            }
        }

        return '-';
    }
}

