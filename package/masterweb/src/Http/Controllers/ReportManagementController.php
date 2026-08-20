<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Exports\ReportAnnualClinicExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportManagementController extends Controller
{
    /** Parameter default di bawah kategori Kimia klinik */
    private const KIMIA_PARAMS = [
        'GDN', 'GD 2 Jam PP', 'GDS', 'HbA1c', 'Cholesterol', 'LDL', 'HDL',
        'Trigliserid', 'Asam Urat', 'Ureum', 'Creatinin', 'SGOT', 'SGPT',
    ];

    public function annualReportClinic(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $tipe = $this->resolveReportClinicTipe($request);

            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            $daysInMonth = $endDate->day;

            $data = $this->getDailyReportData($month, $year, $tipe);

            return view('masterweb::module.admin.laboratorium.report.v2.klinik.report-annual.index', [
                'data' => $data,
                'month' => $month,
                'year' => $year,
                'daysInMonth' => $daysInMonth,
                'kimiaParams' => $data['kimia_params'] ?? self::KIMIA_PARAMS,
                'otherParams' => $data['other_params'] ?? $this->otherReportParams(),
                'tipe' => $tipe,
                'reportTitle' => $this->reportClinicTitle($tipe),
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * biasa = non-haji, haji = pasien haji saja.
     */
    private function resolveReportClinicTipe(Request $request): string
    {
        $routeName = (string) optional($request->route())->getName();
        if (strpos($routeName, 'report-annual-clinic-haji') !== false) {
            return 'haji';
        }

        $path = trim($request->path(), '/');
        if (strpos($path, 'report-annual-clinic-haji') !== false) {
            return 'haji';
        }

        $tipe = strtolower(trim((string) $request->get('tipe', 'biasa')));

        return $tipe === 'haji' ? 'haji' : 'biasa';
    }

    private function reportClinicTitle(string $tipe): string
    {
        return $tipe === 'haji'
            ? 'Catatan Harian Pemeriksaan Unit Klinik (Haji)'
            : 'Catatan Harian Pemeriksaan Unit Klinik';
    }

    private function otherReportParams(): array
    {
        return [
            'Darah rutin',
            'Hemoglobin',
            'LED',
            'Widal',
            'Golongan darah',
            'HBsAg',
            'Urin rutin',
            'Tes Kehamilan',
            'Tes Narkoba',
            'NS1',
            'Dengue IgG/IgM',
            'Typhi IgG/IgM',
            'Croschek TB',
            'Feses',
        ];
    }

    private function getDailyReportData($month, $year, string $tipe = 'biasa')
    {
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $daysInMonth = $endDate->day;

        $reportData = [
            'jumlah_pasien' => array_fill(1, $daysInMonth, 0),
            'parameters' => [],
            'kimia_klinik' => array_fill(1, $daysInMonth, 0),
            'darah_rutin' => array_fill(1, $daysInMonth, 0),
            'urin_rutin' => array_fill(1, $daysInMonth, 0),
            'kimia_params' => self::KIMIA_PARAMS,
            'other_params' => $this->otherReportParams(),
        ];

        $permohonanList = PermohonanUjiKlinik2::query()
            ->whereNull('deleted_at')
            ->whereNotNull('tglregister_permohonan_uji_klinik')
            ->whereYear('tglregister_permohonan_uji_klinik', $year)
            ->whereMonth('tglregister_permohonan_uji_klinik', $month);

        if ($tipe === 'haji') {
            $permohonanList->where('is_haji', 1);
        } else {
            $permohonanList->where(function ($q) {
                $q->whereNull('is_haji')->orWhere('is_haji', 0);
            });
        }

        $permohonanList = $permohonanList->get();

        foreach ($permohonanList as $permohonan) {
            $day = $this->resolvePermohonanReportDay($permohonan, $month, $year);
            if ($day !== null) {
                $reportData['jumlah_pasien'][$day]++;
            }
        }

        $permohonanIds = $permohonanList->pluck('id_permohonan_uji_klinik')->toArray();
        if (empty($permohonanIds)) {
            return $reportData;
        }

        $parameters = PermohonanUjiParameterKlinik::whereIn('permohonan_uji_klinik', $permohonanIds)
            ->whereNull('deleted_at')
            ->with([
                'parametersatuanklinik.parameterjenisklinik',
                'parameterpaketklinik',
                'jenisparameterklinik',
                'permohonanujiklinik',
            ])
            ->get();

        $parameterMap = [];
        $countedKeys = [];
        $kimiaPatientDay = [];
        $darahPatientDay = [];
        $urinPatientDay = [];
        $narkobaPatientDay = [];
        $agregatPatientDay = [];
        $hemoglobinPatientDay = [];
        $hemoglobinDaily = array_fill(1, $daysInMonth, 0);
        $narkobaDaily = array_fill(1, $daysInMonth, 0);
        $labelMeta = [];

        foreach ($parameters as $param) {
            $paramName = $param->parametersatuanklinik->name_parameter_satuan_klinik ?? '';
            $paketModel = $param->parameterpaketklinik;
            $paketName = $paketModel->name_parameter_paket_klinik ?? '';

            if ($paramName === '' && $paketName === '') {
                continue;
            }

            // Paket disembunyikan dari laporan (tampil_di_laporan = 0)
            if ($paketModel && !$this->isPaketTampilDiLaporan($paketModel)) {
                continue;
            }

            $jenisName = $param->jenisparameterklinik->name_parameter_jenis_klinik
                ?? ($param->parametersatuanklinik->parameterjenisklinik->name_parameter_jenis_klinik ?? null);
            $permohonan = $param->permohonanujiklinik;
            if (!$permohonan) {
                continue;
            }

            $day = $this->resolvePermohonanReportDay($permohonan, $month, $year);
            if ($day === null) {
                continue;
            }

            $patientDayKey = $permohonan->id_permohonan_uji_klinik . '|' . $day;
            $isAgregatPaket = $this->isAgregatLaporanPaket($paketModel, $paketName, $jenisName);

            if ($this->isKimiaKlinikJenis($jenisName, $paramName, $paketName, $paketModel)) {
                if (!isset($kimiaPatientDay[$patientDayKey])) {
                    $kimiaPatientDay[$patientDayKey] = true;
                    $reportData['kimia_klinik'][$day]++;
                }
            }

            if ($this->isDarahRutinJenis($jenisName, $paramName, $paketName, $paketModel)) {
                if (!isset($darahPatientDay[$patientDayKey])) {
                    $darahPatientDay[$patientDayKey] = true;
                    $reportData['darah_rutin'][$day]++;
                }
            }

            if ($this->isUrinRutinJenis($jenisName, $paramName, $paketName, $paketModel)) {
                if (!isset($urinPatientDay[$patientDayKey])) {
                    $urinPatientDay[$patientDayKey] = true;
                    $reportData['urin_rutin'][$day]++;
                }
            }

            if ($this->isNarkobaJenis($jenisName, $paramName, $paketName, $paketModel)) {
                if (!isset($narkobaPatientDay[$patientDayKey])) {
                    $narkobaPatientDay[$patientDayKey] = true;
                    $narkobaDaily[$day]++;
                }
            }

            if ($this->isHemoglobinPaketOnly($paketName, $paramName, $paketModel)) {
                if (!isset($hemoglobinPatientDay[$patientDayKey])) {
                    $hemoglobinPatientDay[$patientDayKey] = true;
                    $hemoglobinDaily[$day]++;
                }
            }

            // Paket gabungan: hitung 1x, jangan pecah ke satuan
            if ($isAgregatPaket) {
                $agregatLabel = $this->resolvePaketSingkatan($paketModel, $paketName)
                    ?: $this->resolveReportParameterLabel($paramName, $paketName, $jenisName, $paketModel);

                if ($agregatLabel && !in_array($agregatLabel, ['Darah rutin', 'Urin rutin', 'Tes Narkoba', 'Kimia klinik', 'Hemoglobin'], true)) {
                    $agKey = $patientDayKey . '|' . $agregatLabel;
                    if (!isset($agregatPatientDay[$agKey])) {
                        $agregatPatientDay[$agKey] = true;
                        if (!isset($parameterMap[$agregatLabel])) {
                            $parameterMap[$agregatLabel] = array_fill(1, $daysInMonth, 0);
                        }
                        $parameterMap[$agregatLabel][$day]++;
                        $labelMeta[$agregatLabel] = [
                            'kategori' => $this->resolveLabelKategori($paketModel, $agregatLabel, $jenisName),
                        ];
                    }
                }
                continue;
            }

            $normalizedName = $this->resolveReportParameterLabel($paramName, $paketName, $jenisName, $paketModel);
            if ($normalizedName === null) {
                continue;
            }

            if (in_array($normalizedName, ['Hemoglobin', 'Darah rutin', 'Urin rutin', 'Tes Narkoba', 'Kimia klinik'], true)) {
                continue;
            }

            $dedupeKey = $permohonan->id_permohonan_uji_klinik . '|' . $normalizedName;
            if (isset($countedKeys[$dedupeKey])) {
                continue;
            }
            $countedKeys[$dedupeKey] = true;

            if (!isset($parameterMap[$normalizedName])) {
                $parameterMap[$normalizedName] = array_fill(1, $daysInMonth, 0);
            }
            $parameterMap[$normalizedName][$day]++;
            $labelMeta[$normalizedName] = [
                'kategori' => $this->resolveLabelKategori($paketModel, $normalizedName, $jenisName),
            ];
        }

        $parameterMap['Darah rutin'] = $reportData['darah_rutin'];
        $parameterMap['Urin rutin'] = $reportData['urin_rutin'];
        $parameterMap['Hemoglobin'] = $hemoglobinDaily;
        $parameterMap['Tes Narkoba'] = $narkobaDaily;

        // Pastikan semua paket yang "Tampil" di pengaturan muncul sebagai baris
        // (meski belum ada data di bulan tersebut).
        $configured = $this->loadConfiguredLaporanLabels($daysInMonth);
        foreach ($configured['labels'] as $label => $meta) {
            if (!isset($parameterMap[$label])) {
                $parameterMap[$label] = array_fill(1, $daysInMonth, 0);
            }
            if (!isset($labelMeta[$label])) {
                $labelMeta[$label] = $meta;
            }
        }

        $reportData['parameters'] = $parameterMap;
        $reportData['kimia_params'] = $this->buildKimiaParams($parameterMap, $labelMeta, $configured['kimia']);
        $reportData['other_params'] = $this->buildOtherParams($parameterMap, $labelMeta, $reportData['kimia_params'], $configured['lain']);

        return $reportData;
    }

    /**
     * Ambil label laporan dari master paket yang tampil + punya singkatan_laporan.
     * Hanya paket dengan singkatan eksplisit yang dipaksa muncul sebagai baris
     * (meski belum ada data), supaya subunit tanpa singkatan (Bau, Leukosit, dll)
     * tidak membanjiri tabel.
     *
     * @return array{labels: array<string, array{kategori: string}>, kimia: string[], lain: string[]}
     */
    private function loadConfiguredLaporanLabels(int $daysInMonth): array
    {
        $reserved = [
            'Kimia klinik', 'Darah rutin', 'Urin rutin', 'Tes Narkoba', 'Hemoglobin',
        ];

        $pakets = ParameterPaketKlinik::query()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('tampil_di_laporan', 1)->orWhereNull('tampil_di_laporan');
            })
            ->whereNotNull('singkatan_laporan')
            ->where('singkatan_laporan', '!=', '')
            ->orderBy('name_parameter_paket_klinik', 'asc')
            ->get([
                'name_parameter_paket_klinik',
                'singkatan_laporan',
                'kategori_laporan',
                'is_agregat_laporan',
                'tampil_di_laporan',
            ]);

        $labels = [];
        $kimia = [];
        $lain = [];

        foreach ($pakets as $paket) {
            if ((int) ($paket->tampil_di_laporan ?? 1) === 0) {
                continue;
            }

            $label = trim((string) ($paket->singkatan_laporan ?? ''));
            if ($label === '' || in_array($label, $reserved, true)) {
                continue;
            }

            // Agregat Darah/Urin/Narkoba/Kimia panel: jangan dobel sebagai baris satuan
            if ((int) ($paket->is_agregat_laporan ?? 0) === 1) {
                $lower = strtolower($label);
                if (preg_match('/darah\s*rutin|urin\s*rutin|tes\s*narkoba|kimia\s*klinik/', $lower)) {
                    continue;
                }
            }

            $kategori = $this->resolveLabelKategori($paket, $label, null);
            $labels[$label] = ['kategori' => $kategori];

            if ($kategori === 'kimia') {
                if (!in_array($label, $kimia, true) && !in_array($label, self::KIMIA_PARAMS, true)) {
                    $kimia[] = $label;
                }
            } else {
                if (!in_array($label, $lain, true) && !in_array($label, $this->otherReportParams(), true)) {
                    $lain[] = $label;
                }
            }
        }

        return [
            'labels' => $labels,
            'kimia' => $kimia,
            'lain' => $lain,
        ];
    }

    /**
     * Default tampil. Hanya disembunyikan jika eksplisit tampil_di_laporan = 0.
     */
    private function isPaketTampilDiLaporan($paketModel): bool
    {
        if (!$paketModel) {
            return true;
        }

        return (int) ($paketModel->tampil_di_laporan ?? 1) !== 0;
    }

    private function buildKimiaParams(array $parameterMap, array $labelMeta, array $configuredKimia = []): array
    {
        $kimia = self::KIMIA_PARAMS;

        foreach ($configuredKimia as $label) {
            if (!in_array($label, $kimia, true) && $label !== 'Kimia klinik') {
                $kimia[] = $label;
            }
        }

        foreach ($parameterMap as $label => $_) {
            $kategori = $labelMeta[$label]['kategori'] ?? null;
            if ($kategori === 'kimia' && !in_array($label, $kimia, true) && $label !== 'Kimia klinik') {
                $kimia[] = $label;
            }
        }

        return $kimia;
    }

    private function buildOtherParams(array $parameterMap, array $labelMeta, array $kimiaParams, array $configuredLain = []): array
    {
        $other = $this->otherReportParams();
        $known = array_merge($kimiaParams, $other, ['Kimia klinik']);

        foreach ($configuredLain as $label) {
            if (!in_array($label, $known, true)) {
                $other[] = $label;
                $known[] = $label;
            }
        }

        foreach (array_keys($parameterMap) as $label) {
            if (in_array($label, $known, true)) {
                continue;
            }
            $kategori = $labelMeta[$label]['kategori'] ?? 'lain';
            if ($kategori === 'kimia') {
                continue;
            }
            $other[] = $label;
            $known[] = $label;
        }

        return $other;
    }

    private function resolveLabelKategori($paketModel, string $label, $jenisName = null): string
    {
        if ($paketModel && !empty($paketModel->kategori_laporan)) {
            return $paketModel->kategori_laporan === 'kimia' ? 'kimia' : 'lain';
        }
        if (in_array($label, self::KIMIA_PARAMS, true)) {
            return 'kimia';
        }
        $jenis = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $jenisName)));
        if ($jenis !== '' && preg_match('/kimia\s*klinik/', $jenis) && !preg_match('/kimia\s*urin/', $jenis)) {
            return 'kimia';
        }

        return 'lain';
    }

    private function resolvePaketSingkatan($paketModel, string $paketName = ''): ?string
    {
        if ($paketModel && trim((string) ($paketModel->singkatan_laporan ?? '')) !== '') {
            return trim($paketModel->singkatan_laporan);
        }

        return null;
    }

    private function isAgregatLaporanPaket($paketModel, string $paketName = '', $jenisName = null): bool
    {
        if ($paketModel && (int) ($paketModel->is_agregat_laporan ?? 0) === 1) {
            return true;
        }

        $paket = strtolower(trim(preg_replace('/\s+/u', ' ', $paketName)));
        if ($paket !== '' && preg_match('/^(darah\s*rutin|urin\s*rutin|urine\s*rutin|tes\s*narkoba|kimia\s*klinik)\b/', $paket)) {
            return true;
        }

        $jenis = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $jenisName)));
        if ($jenis !== '' && preg_match('/narkoba|narkotika/', $jenis)) {
            return true;
        }

        return false;
    }

    private function isKimiaKlinikJenis($jenisName, string $paramName = '', string $paketName = '', $paketModel = null): bool
    {
        if ($paketModel && ($paketModel->kategori_laporan ?? null) === 'kimia') {
            return true;
        }

        $jenis = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $jenisName)));
        if ($jenis !== '') {
            if (preg_match('/kimia\s*urin/', $jenis)) {
                return false;
            }
            if (preg_match('/kimia\s*klinik/', $jenis)) {
                return true;
            }
        }

        $singkatan = $this->resolvePaketSingkatan($paketModel, $paketName);
        if ($singkatan !== null && in_array($singkatan, self::KIMIA_PARAMS, true)) {
            return true;
        }

        foreach ([$paramName, $paketName] as $candidate) {
            if ($candidate === '') {
                continue;
            }
            $label = $this->normalizeParameterName($candidate, $jenisName);
            if ($label !== null && in_array($label, self::KIMIA_PARAMS, true)) {
                return true;
            }
        }

        return false;
    }

    private function isDarahRutinJenis($jenisName, string $paramName = '', string $paketName = '', $paketModel = null): bool
    {
        if ($this->resolvePaketSingkatan($paketModel, $paketName) === 'Darah rutin') {
            return true;
        }

        $jenis = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $jenisName)));
        if ($jenis !== '' && preg_match('/darah\s*rutin/', $jenis)) {
            return true;
        }

        foreach ([$paramName, $paketName] as $candidate) {
            $name = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $candidate)));
            if ($name !== '' && preg_match('/^darah\s*rutin/', $name)) {
                return true;
            }
        }

        return false;
    }

    private function isUrinRutinJenis($jenisName, string $paramName = '', string $paketName = '', $paketModel = null): bool
    {
        if ($this->resolvePaketSingkatan($paketModel, $paketName) === 'Urin rutin') {
            return true;
        }

        $jenis = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $jenisName)));
        if ($jenis !== '' && preg_match('/kimia\s*urin/', $jenis)) {
            return false;
        }
        if ($jenis !== '' && preg_match('/urin\s*rutin|urine\s*rutin/', $jenis)) {
            return true;
        }

        foreach ([$paramName, $paketName] as $candidate) {
            $name = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $candidate)));
            if ($name !== '' && preg_match('/^urin\s*rutin|^urine\s*rutin/', $name)) {
                return true;
            }
        }

        return false;
    }

    private function isNarkobaJenis($jenisName, string $paramName = '', string $paketName = '', $paketModel = null): bool
    {
        if ($this->resolvePaketSingkatan($paketModel, $paketName) === 'Tes Narkoba') {
            return true;
        }

        $jenis = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $jenisName)));
        if ($jenis !== '' && preg_match('/narkoba|narkotika/', $jenis)) {
            return true;
        }

        foreach ([$paramName, $paketName] as $candidate) {
            $name = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $candidate)));
            if ($name !== '' && preg_match('/tes\s*narkoba|test\s*narkoba|narkoba|narkotika/', $name)) {
                return true;
            }
        }

        return false;
    }

    private function isHemoglobinPaketOnly(string $paketName, string $paramName = '', $paketModel = null): bool
    {
        $singkatan = $this->resolvePaketSingkatan($paketModel, $paketName);
        $paket = strtolower(trim(preg_replace('/\s+/u', ' ', $paketName)));

        if ($singkatan === 'Hemoglobin') {
            return $paket === '' || !preg_match('/darah\s*rutin/', $paket);
        }

        if ($paket === '') {
            return false;
        }
        if (preg_match('/darah\s*rutin/', $paket)) {
            return false;
        }
        if (preg_match('/hba1c|hemoglobin\s*a1c/', $paket)) {
            return false;
        }

        return (bool) preg_match('/\bhemoglobin\b|^hb\b|\(hemoglobin\)/', $paket);
    }

    private function resolvePermohonanReportDate(PermohonanUjiKlinik2 $permohonan): ?Carbon
    {
        if (empty($permohonan->tglregister_permohonan_uji_klinik)) {
            return null;
        }

        return Carbon::parse($permohonan->tglregister_permohonan_uji_klinik);
    }

    private function resolvePermohonanReportDay(PermohonanUjiKlinik2 $permohonan, $month, $year): ?int
    {
        $date = $this->resolvePermohonanReportDate($permohonan);
        if ($date === null) {
            return null;
        }

        if ((int) $date->year !== (int) $year || (int) $date->month !== (int) $month) {
            return null;
        }

        return (int) $date->day;
    }

    /**
     * Prioritas: singkatan_laporan → aturan nama lama → nama paket dibersihkan.
     */
    private function resolveReportParameterLabel(string $satuanName, string $paketName, $jenisName = null, $paketModel = null): ?string
    {
        $singkatan = $this->resolvePaketSingkatan($paketModel, $paketName);
        if ($singkatan !== null) {
            return $singkatan;
        }

        foreach ([$satuanName, $paketName] as $candidate) {
            if ($candidate === '') {
                continue;
            }
            $label = $this->normalizeParameterName($candidate, $jenisName);
            if ($label !== null) {
                return $label;
            }
        }

        $auto = $this->cleanPaketNameForReport($paketName !== '' ? $paketName : $satuanName);

        return $auto !== '' ? $auto : null;
    }

    private function cleanPaketNameForReport(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        $name = preg_replace('/\s*\((klaim|bpjs)\)\s*$/iu', '', $name);

        return trim($name);
    }

    private function normalizeParameterName($name, $jenisName = null)
    {
        $jenis = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $jenisName)));
        $name = strtolower(trim(preg_replace('/\s+/u', ' ', (string) $name)));

        if ($name === '' && $jenis === '') {
            return null;
        }

        if ($name !== '') {
            if (preg_match('/salmonella\s*typhi\s*igg?\/?igm|typhi\s*igg?\/?igm/', $name)) {
                return 'Typhi IgG/IgM';
            }
            if (preg_match('/anti\s*dengue|dengue\s*igg?\/?igm|\bdengue\b.*\b(igg|igm)\b/', $name)) {
                return 'Dengue IgG/IgM';
            }
            if (preg_match('/\bns1\b|antigen\s*ns1|dengue\s*ns1|rapid\s*tes\s*ns1/', $name)) {
                return 'NS1';
            }
            if (preg_match('/\btyphi\s*[ho]\b|salmonella\s*typhi\s*[ho]\b/', $name)) {
                return 'Widal';
            }
        }

        if ($jenis !== '') {
            if (preg_match('/narkoba|narkotika/', $jenis)) {
                return 'Tes Narkoba';
            }
            if (preg_match('/feses/', $jenis)) {
                return 'Feses';
            }
            if (preg_match('/kehamilan/', $jenis)) {
                return 'Tes Kehamilan';
            }
            if (preg_match('/widal/', $jenis)) {
                if (!($name !== '' && preg_match('/ns1|igg|igm|hiv|dengue/', $name))) {
                    return 'Widal';
                }
            }
        }

        if ($name === '') {
            return null;
        }

        if ($this->isGulaDarah2JamPpName($name)) {
            return 'GD 2 Jam PP';
        }

        $rules = [
            ['pattern' => '/^(gdn|gdp)\b|gula darah puasa/', 'label' => 'GDN'],
            ['pattern' => '/gula darah sewaktu|\bgds\b/', 'label' => 'GDS'],
            ['pattern' => '/hba1c|hemoglobin a1c/', 'label' => 'HbA1c'],
            ['pattern' => '/\bhdl\b|hdl cholesterol/', 'label' => 'HDL'],
            ['pattern' => '/\bldl\b|ldl cholesterol/', 'label' => 'LDL'],
            ['pattern' => '/trigliserid|triglyceride/', 'label' => 'Trigliserid'],
            ['pattern' => '/cholesterol total|cholesterol|kolesterol/', 'label' => 'Cholesterol'],
            ['pattern' => '/asam urat/', 'label' => 'Asam Urat'],
            ['pattern' => '/\bureum\b|\burea\b/', 'label' => 'Ureum'],
            ['pattern' => '/kreatinin|creatinin|creatinine/', 'label' => 'Creatinin'],
            ['pattern' => '/\bsgot\b|\bast\b/', 'label' => 'SGOT'],
            ['pattern' => '/\bsgpt\b|\balt\b/', 'label' => 'SGPT'],
            ['pattern' => '/\bhemoglobin\b/', 'label' => 'Hemoglobin'],
            ['pattern' => '/\bled\b|laju endap/', 'label' => 'LED'],
            ['pattern' => '/\bwidal\b/', 'label' => 'Widal'],
            ['pattern' => '/golongan darah|gol\.?\s*darah/', 'label' => 'Golongan darah'],
            ['pattern' => '/\bhbsag\b/', 'label' => 'HBsAg'],
            ['pattern' => '/pp tes|tes kehamilan|test kehamilan|\bhcg\b/', 'label' => 'Tes Kehamilan'],
            ['pattern' => '/amphetamine|\bamp\b|\bmet\b|methamphetamine|meth - amphetamine/', 'label' => 'Tes Narkoba'],
            ['pattern' => '/cocaine|\bcoc\b/', 'label' => 'Tes Narkoba'],
            ['pattern' => '/morphine|\bmop\b/', 'label' => 'Tes Narkoba'],
            ['pattern' => '/benzodiazepine|\bbzo\b/', 'label' => 'Tes Narkoba'],
            ['pattern' => '/cannabinoid|marijuana|\bthc\b/', 'label' => 'Tes Narkoba'],
            ['pattern' => '/tes narkoba|test narkoba|narkoba|narkotika/', 'label' => 'Tes Narkoba'],
            ['pattern' => '/croschek|chestek|gene xpert|genexpert|sputum bta/', 'label' => 'Croschek TB'],
            ['pattern' => '/\bfeses\b|faeces|feces|feses rutin/', 'label' => 'Feses'],
        ];

        foreach ($rules as $rule) {
            if (preg_match($rule['pattern'], $name)) {
                return $rule['label'];
            }
        }

        return null;
    }

    private function isGulaDarah2JamPpName(string $name): bool
    {
        return (bool) preg_match(
            '/gula\s*darah\s*2\s*j(?:am)?\s*pp|gd\s*2\s*jam\s*pp|gd\s*2jpp|gula\s*darah\s*2jpp|^2\s*jam\s*pp/',
            $name
        );
    }

    public function exportAnnualClinic(Request $request)
    {
        try {
            $exportType = $request->get('export_type', 'month');
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $tipe = $this->resolveReportClinicTipe($request);

            $data = [];
            $isYearly = false;

            if ($exportType == 'year') {
                $isYearly = true;
                for ($m = 1; $m <= 12; $m++) {
                    $data[$m] = $this->getDailyReportData(sprintf('%02d', $m), $year, $tipe);
                }
            } else {
                $data[$month] = $this->getDailyReportData($month, $year, $tipe);
            }

            $export = new ReportAnnualClinicExport($data, $month, $year, $isYearly, $tipe);

            $suffix = $tipe === 'haji' ? '_Haji' : '';
            $filename = $isYearly
                ? "Laporan_Klinik_Tahunan{$suffix}_{$year}.xlsx"
                : "Laporan_Klinik{$suffix}_" . fbulan($month) . "_{$year}.xlsx";

            return Excel::download($export, $filename);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Daftar paket untuk pengaturan laporan (singkatan / tampil / kategori / agregat).
     */
    public function getPaketLaporanSettings()
    {
        $pakets = ParameterPaketKlinik::query()
            ->whereNull('deleted_at')
            ->orderBy('name_parameter_paket_klinik', 'asc')
            ->get([
                'id_parameter_paket_klinik',
                'name_parameter_paket_klinik',
                'singkatan_laporan',
                'kategori_laporan',
                'is_agregat_laporan',
                'tampil_di_laporan',
            ]);

        $rows = $pakets->map(function ($p) {
            return [
                'id' => $p->id_parameter_paket_klinik,
                'nama' => $p->name_parameter_paket_klinik,
                'singkatan' => $p->singkatan_laporan ?? '',
                'kategori' => $p->kategori_laporan ?? '',
                'is_agregat' => (int) ($p->is_agregat_laporan ?? 0) === 1,
                'tampil' => (int) ($p->tampil_di_laporan ?? 1) !== 0,
            ];
        })->values();

        return response()->json(['status' => true, 'data' => $rows]);
    }

    /**
     * Simpan pengaturan paket laporan dari halaman report-annual-clinic(-haji).
     */
    public function savePaketLaporanSettings(Request $request)
    {
        $items = $request->input('items', []);
        if (!is_array($items) || empty($items)) {
            return response()->json(['status' => false, 'pesan' => 'Tidak ada data yang dikirim.'], 422);
        }

        $updated = 0;
        foreach ($items as $item) {
            $id = $item['id'] ?? null;
            if (!$id) {
                continue;
            }

            $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $id)
                ->whereNull('deleted_at')
                ->first();
            if (!$paket) {
                continue;
            }

            $singkatan = isset($item['singkatan']) ? trim((string) $item['singkatan']) : '';
            $kategori = isset($item['kategori']) ? trim((string) $item['kategori']) : '';
            if ($kategori !== '' && !in_array($kategori, ['kimia', 'lain'], true)) {
                $kategori = '';
            }

            $paket->singkatan_laporan = $singkatan !== '' ? $singkatan : null;
            $paket->kategori_laporan = $kategori !== '' ? $kategori : null;
            $paket->is_agregat_laporan = !empty($item['is_agregat']) ? 1 : 0;
            $paket->tampil_di_laporan = !empty($item['tampil']) ? 1 : 0;
            $paket->save();
            $updated++;
        }

        return response()->json([
            'status' => true,
            'pesan' => "Pengaturan {$updated} paket berhasil disimpan.",
        ]);
    }
}
