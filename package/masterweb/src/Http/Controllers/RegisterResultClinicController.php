<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\KlinikNumberSettings;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\RegisterHasilKlinikKolom;
use Smt\Masterweb\Helpers\Smt;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class RegisterResultClinicController extends Controller
{
    /** Alias pendek: hanya exact match (fallback jika belum ada link satuan) */
    private const EXACT_ONLY_KEYS = [
        'ot', 'pt', 'ur', 'au', 'tg', 'pp', 'tp', 'ck', 'na', 'cl', 'ca', 'mg', 'k',
        'hb', 'ht', 'bj', 'ph', 'uro', 'ppt', 'esr', 'led', 'eos', 'neu', 'plt',
        'mch', 'mcv', 'hdl', 'ldl', 'alp', 'ggt', 'ldh', 'gds', 'gdn', 'cre', 'alb',
        'ast', 'alt', 'baso', 'mono', 'limfo',
    ];

    public function index(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $layout = RegisterHasilKlinikKolom::buildVisibleLayout();
            $data = $this->getRegisterData($month, $year, $layout);

            return view('masterweb::module.admin.laboratorium.report.v2.klinik.register-result.index', [
                'data' => $data,
                'month' => $month,
                'year' => $year,
                'columnGroups' => $layout['groups'],
                'columnTotal' => $layout['total'],
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $layout = RegisterHasilKlinikKolom::buildVisibleLayout();
            $data = $this->getRegisterData($month, $year, $layout);

            $filename = 'Register_Hasil_Klinis_' . \Smt\Masterweb\Helpers\Smt::fbulan(sprintf('%02d', $month)) . '_' . $year . '.xlsx';

            return Excel::download(
                new \Smt\Masterweb\Exports\RegisterResultClinicExport($data, $month, $year, $layout),
                $filename
            );
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function getKolomSettings()
    {
        $satuans = ParameterSatuanKlinik::query()
            ->whereNull('deleted_at')
            ->orderBy('name_parameter_satuan_klinik')
            ->get(['id_parameter_satuan_klinik', 'name_parameter_satuan_klinik'])
            ->map(function ($s) {
                return [
                    'id' => $s->id_parameter_satuan_klinik,
                    'nama' => $s->name_parameter_satuan_klinik,
                ];
            })
            ->values();

        $rows = RegisterHasilKlinikKolom::allSettingsColumns()->map(function ($col) {
            $linked = $col->parametersatuan->map(function ($s) {
                return [
                    'id' => $s->id_parameter_satuan_klinik,
                    'nama' => $s->name_parameter_satuan_klinik,
                ];
            })->values()->all();

            return [
                'id' => $col->id_register_hasil_klinis_kolom,
                'nama' => $col->label ?: $col->kode,
                'kode' => $col->kode,
                'label' => $col->label,
                'grup' => $col->grup,
                'grup_label' => RegisterHasilKlinikKolom::GRUP_LABELS[$col->grup] ?? $col->grup,
                'sort' => (int) $col->sort,
                'tampil' => (int) ($col->tampil ?? 1) !== 0,
                'satuan_ids' => array_column($linked, 'id'),
                'satuan' => $linked,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $rows,
            'grup_options' => RegisterHasilKlinikKolom::GRUP_LABELS,
            'satuan_options' => $satuans,
        ]);
    }

    public function saveKolomSettings(Request $request)
    {
        $items = $request->input('items', []);
        if (!is_array($items) || empty($items)) {
            return response()->json(['status' => false, 'pesan' => 'Tidak ada data yang dikirim.'], 422);
        }

        $allowedGrup = array_keys(RegisterHasilKlinikKolom::GRUP_LABELS);
        $updated = 0;
        $created = 0;
        $deleted = 0;

        foreach ($items as $item) {
            $id = isset($item['id']) ? trim((string) $item['id']) : '';
            $isNew = ($id === '' || strpos($id, 'new-') === 0);
            $willDelete = !empty($item['hapus']);

            $nama = isset($item['nama']) ? trim((string) $item['nama']) : '';
            if ($nama === '' && isset($item['label'])) {
                $nama = trim((string) $item['label']);
            }
            if ($nama === '' && isset($item['kode'])) {
                $nama = trim((string) $item['kode']);
            }

            if ($willDelete) {
                if ($isNew) {
                    continue;
                }
                $col = RegisterHasilKlinikKolom::where('id_register_hasil_klinis_kolom', $id)->first();
                if ($col) {
                    $col->parametersatuan()->detach();
                    $col->forceDelete();
                    $deleted++;
                }
                continue;
            }

            if ($nama === '') {
                continue;
            }

            $grup = isset($item['grup']) ? trim((string) $item['grup']) : 'other';
            if (!in_array($grup, $allowedGrup, true)) {
                $grup = 'other';
            }

            $sort = isset($item['sort']) ? (int) $item['sort'] : 0;
            $tampil = !empty($item['tampil']) ? 1 : 0;

            // Cek unik nama (kode = label)
            $dupQuery = RegisterHasilKlinikKolom::where('kode', $nama);
            if (!$isNew) {
                $dupQuery->where('id_register_hasil_klinis_kolom', '!=', $id);
            }
            if ($dupQuery->exists()) {
                return response()->json([
                    'status' => false,
                    'pesan' => "Nama kolom \"{$nama}\" sudah dipakai. Gunakan nama lain.",
                ], 422);
            }

            if ($isNew) {
                $maxSort = (int) RegisterHasilKlinikKolom::max('sort');
                $col = new RegisterHasilKlinikKolom();
                $col->kode = $nama;
                $col->label = $nama;
                $col->grup = $grup;
                $col->sort = $sort > 0 ? $sort : ($maxSort + 1);
                $col->tampil = $tampil;
                $col->match_keys = [strtolower($nama)];
                $col->save();
                $created++;
            } else {
                $col = RegisterHasilKlinikKolom::where('id_register_hasil_klinis_kolom', $id)->first();
                if (!$col) {
                    continue;
                }
                $col->kode = $nama;
                $col->label = $nama;
                $col->grup = $grup;
                $col->tampil = $tampil;
                $col->sort = $sort;
                $col->save();
                $updated++;
            }

            $satuanIds = $item['satuan_ids'] ?? [];
            if (!is_array($satuanIds)) {
                $satuanIds = [];
            }
            $satuanIds = array_values(array_unique(array_filter(array_map('strval', $satuanIds))));
            $col->parametersatuan()->sync($satuanIds);
        }

        $pesan = [];
        if ($created) {
            $pesan[] = "{$created} kolom ditambah";
        }
        if ($updated) {
            $pesan[] = "{$updated} kolom diubah";
        }
        if ($deleted) {
            $pesan[] = "{$deleted} kolom dihapus";
        }
        if (empty($pesan)) {
            $pesan[] = 'Tidak ada perubahan';
        }

        return response()->json([
            'status' => true,
            'pesan' => implode(', ', $pesan) . '.',
        ]);
    }

    private function getRegisterData($month, $year, array $layout)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $klinikNumberSettings = KlinikNumberSettings::getSettings();

        $permohonanList = PermohonanUjiKlinik2::whereBetween(DB::raw('DATE(created_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNull('deleted_at')
            ->where('done_register', true)
            ->with(['pasien', 'permohonanujiparameterklinik' => function ($query) {
                $query->whereNull('deleted_at')
                    ->where('status_verifikasi', 'approved')
                    ->with(['parametersatuanklinik.parameterjenisklinik', 'jenisparameterklinik', 'unit']);
            }])
            ->orderByNomerSpesimen('asc', $klinikNumberSettings)
            ->get();

        $registerData = [];

        foreach ($permohonanList as $index => $permohonan) {
            $pasien = $permohonan->pasien;
            $noSpesimen = $permohonan->getDisplayNoregister($klinikNumberSettings);
            $parameters = $permohonan->permohonanujiparameterklinik;
            $results = $this->mapParametersToResults($parameters, $layout);

            $registerData[] = [
                'no' => $index + 1,
                'tanggal' => Carbon::parse($permohonan->created_at)->format('d/m/Y'),
                'no_spesimen' => $noSpesimen,
                'no_rm' => $pasien->no_rekammedis_pasien ?? '-',
                'nama_pasien' => $pasien->nama_pasien ?? '-',
                'umur' => $permohonan->umurtahun_pasien_permohonan_uji_klinik ?? ($pasien->umurtahun_pasien ?? '-'),
                'alamat' => Smt::alamatPasienCetak($pasien),
                'results' => $results,
            ];
        }

        return $registerData;
    }

    private function emptyResults(array $layout): array
    {
        $results = [];
        foreach ($layout['groups'] as $group) {
            $results[$group['key']] = [];
            foreach ($group['columns'] as $col) {
                $results[$group['key']][$col['kode']] = '';
            }
        }

        return $results;
    }

    private function mapParametersToResults($parameters, array $layout)
    {
        $results = $this->emptyResults($layout);
        $satuanMap = $layout['satuan_map'] ?? [];

        // Fallback nama (jika satuan belum di-link)
        $mappingsByGrup = [];
        foreach ($layout['groups'] as $group) {
            $map = [];
            foreach ($group['columns'] as $col) {
                foreach ($col['match_keys'] as $key) {
                    $key = strtolower(trim((string) $key));
                    if ($key !== '') {
                        $map[$key] = $col['kode'];
                    }
                }
                $fallback = strtolower(trim($col['kode']));
                if ($fallback !== '' && !isset($map[$fallback])) {
                    $map[$fallback] = $col['kode'];
                }
            }
            $mappingsByGrup[$group['key']] = $map;
        }

        foreach ($parameters as $param) {
            if (!$param->parametersatuanklinik) {
                continue;
            }

            $satuanId = $param->parameter_satuan_klinik
                ?? ($param->parametersatuanklinik->id_parameter_satuan_klinik ?? null);
            $paramName = strtolower(trim($param->parametersatuanklinik->name_parameter_satuan_klinik));
            $hasil = $param->hasil_permohonan_uji_parameter_klinik ?? '';
            $jenisName = strtolower(trim(
                $param->parametersatuanklinik->parameterjenisklinik->name_parameter_jenis_klinik
                    ?? ($param->jenisparameterklinik->name_parameter_jenis_klinik ?? '')
            ));

            // Prioritas 1: link langsung ke parameter satuan
            if ($satuanId && isset($satuanMap[$satuanId])) {
                $target = $satuanMap[$satuanId];
                $grup = $target['grup'];
                $kode = $target['kode'];
                if (isset($results[$grup][$kode])) {
                    $results[$grup][$kode] = $hasil;
                }
                continue;
            }

            if (preg_match('/^lain[\s\-]*lain$/', $paramName)) {
                if (preg_match('/urin|sedimen/', $jenisName) && isset($results['urin_rutin']['Lain"'])) {
                    $results['urin_rutin']['Lain"'] = $hasil;
                } elseif (isset($results['other']['Lain-lain'])) {
                    $results['other']['Lain-lain'] = $hasil;
                }
                continue;
            }

            // Prioritas 2: fallback cocok nama (untuk kolom yang belum di-link)
            $groupOrder = $this->resolveGroupOrder($jenisName);
            $mapped = false;
            foreach ($groupOrder as $group) {
                if ($mapped || !isset($mappingsByGrup[$group])) {
                    continue;
                }

                $col = $this->matchMappedColumn($paramName, $mappingsByGrup[$group], self::EXACT_ONLY_KEYS);
                if ($col !== null && array_key_exists($col, $results[$group] ?? [])) {
                    $results[$group][$col] = $hasil;
                    $mapped = true;
                }
            }
        }

        return $results;
    }

    private function resolveGroupOrder(string $jenisName): array
    {
        $default = RegisterHasilKlinikKolom::GRUP_ORDER;

        if (preg_match('/urin|sedimen/', $jenisName)) {
            return ['urin_rutin', 'other', 'kimia_darah', 'darah_rutin', 'widal', 'hbsag'];
        }
        if (preg_match('/darah rutin|hematolog|hitung jenis/', $jenisName)) {
            return ['darah_rutin', 'kimia_darah', 'widal', 'hbsag', 'urin_rutin', 'other'];
        }
        if (preg_match('/kimia/', $jenisName)) {
            return ['kimia_darah', 'darah_rutin', 'widal', 'hbsag', 'urin_rutin', 'other'];
        }
        if (preg_match('/widal|serolog/', $jenisName)) {
            return ['widal', 'hbsag', 'kimia_darah', 'darah_rutin', 'urin_rutin', 'other'];
        }
        if (preg_match('/hbsag|hepatitis/', $jenisName)) {
            return ['hbsag', 'widal', 'kimia_darah', 'darah_rutin', 'urin_rutin', 'other'];
        }
        if (preg_match('/feses|narkoba|kehamilan|ppt/', $jenisName)) {
            return ['other', 'urin_rutin', 'kimia_darah', 'darah_rutin', 'widal', 'hbsag'];
        }

        return $default;
    }

    private function matchMappedColumn(string $paramName, array $mapping, array $exactOnlyKeys): ?string
    {
        $keys = array_keys($mapping);
        usort($keys, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach ($keys as $key) {
            $isExactOnly = in_array($key, $exactOnlyKeys, true) || strlen($key) <= 2;

            if ($isExactOnly) {
                if ($paramName === $key) {
                    return is_string($mapping[$key]) ? $mapping[$key] : $key;
                }
                continue;
            }

            if (stripos($paramName, $key) !== false) {
                return is_string($mapping[$key]) ? $mapping[$key] : $key;
            }
        }

        return null;
    }
}
