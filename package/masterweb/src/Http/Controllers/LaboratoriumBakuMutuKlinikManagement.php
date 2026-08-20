<?php

namespace Smt\Masterweb\Http\Controllers;

// use Validator;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use Illuminate\Validation\Rule;

use Yajra\Datatables\Datatables;
use \Smt\Masterweb\Models\Method;

// use DB;

use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\BakuMutu;
use App\Http\Controllers\Controller;
use Facade\Ignition\DumpRecorder\Dump;
use Smt\Masterweb\Models\Laboratorium;
use Illuminate\Support\Facades\Validator;
use Smt\Masterweb\Models\ParameterSatuanKlinik;

use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Helpers\BakuMutuPermohonanKlinikHelper;

class LaboratoriumBakuMutuKlinikManagement extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  private function applyNullableWhere($query, string $column, $value)
  {
    if ($value === null || $value === '') {
      return $query->where(function ($q) use ($column) {
        $q->whereNull($column)->orWhere($column, '');
      });
    }
    return $query->where($column, $value);
  }

  /**
   * Cari baris baku mutu berdasarkan signature unik (demografi + range nilai).
   */
  private function findBakuMutuBySignature(array $payload)
  {
    $query = BakuMutu::where('parameter_jenis_klinik_id', $payload['parameter_jenis_klinik_id'])
      ->where('parameter_satuan_klinik_id', $payload['parameter_satuan_klinik_id'])
      ->where('is_sub_parameter_satuan_baku_mutu', (int)($payload['is_sub_parameter_satuan_baku_mutu'] ?? 0))
      ->where('is_khusus_baku_mutu', (int)($payload['is_khusus_baku_mutu'] ?? 0))
      ->where('is_haji', (int)($payload['is_haji'] ?? 0));

    if ((int)($payload['is_khusus_baku_mutu'] ?? 0) === 1) {
      if (isset($payload['is_haji']) && (int)$payload['is_haji'] === 1) {
        $this->applyNullableWhere($query, 'gender_baku_mutu', $payload['gender_baku_mutu'] ?? null);
        $query->whereNull('minimal_umur_baku_mutu')->whereNull('maksimal_umur_baku_mutu');
      } else {
        $this->applyNullableWhere($query, 'gender_baku_mutu', $payload['gender_baku_mutu'] ?? null);
        $this->applyNullableWhere($query, 'minimal_umur_baku_mutu', $payload['minimal_umur_baku_mutu'] ?? null);
        $this->applyNullableWhere($query, 'maksimal_umur_baku_mutu', $payload['maksimal_umur_baku_mutu'] ?? null);
      }
    } else {
      $query->whereNull('gender_baku_mutu')
        ->whereNull('minimal_umur_baku_mutu')
        ->whereNull('maksimal_umur_baku_mutu');
    }

    $this->applyNullableWhere($query, 'min', $payload['min'] ?? null);
    $this->applyNullableWhere($query, 'max', $payload['max'] ?? null);
    $this->applyNullableWhere($query, 'equal', $payload['equal'] ?? null);
    $this->applyNullableWhere($query, 'kesimpulan_baku_mutu', $payload['kesimpulan_baku_mutu'] ?? null);

    return $query;
  }

  private function normalizeIsKhususValue($value): int
  {
    return ($value === 1 || $value === '1' || $value === true || $value === 'true') ? 1 : 0;
  }

  private function normalizeNumericField($value)
  {
    if ($value === null || $value === '') {
      return null;
    }
    return str_replace(',', '.', (string) $value);
  }

  private function applyIsHajiScope($query, int $isHaji)
  {
    if ($isHaji === 1) {
      return $query->where('is_haji', 1);
    }

    return $query->where(function ($q) {
      $q->where('is_haji', 0)->orWhereNull('is_haji');
    });
  }

  private function applySatuanGroupScope($query, string $satuan, int $isHaji)
  {
    $query->where('parameter_satuan_klinik_id', $satuan);

    return $this->applyIsHajiScope($query, $isHaji);
  }

  /**
   * Scope hapus/baca grup massal: seluruh data per parameter satuan (+ is_haji).
   */
  private function applyReplaceGroupScope($query, array $ctx)
  {
    return $this->applySatuanGroupScope($query, $ctx['satuan'], (int) $ctx['is_haji']);
  }

  private function parseReplaceGroupRows(Request $request): array
  {
    $raw = $request->input('rows');

    if (is_array($raw)) {
      return array_values($raw);
    }

    if (!is_string($raw) || trim($raw) === '') {
      return [];
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded) ? array_values($decoded) : [];
  }

  private function groupBakuMutuBySatuanQuery(string $satuan, int $isHaji)
  {
    $query = BakuMutu::query();
    return $this->applySatuanGroupScope($query, $satuan, $isHaji);
  }

  private function groupBakuMutuQuery(string $jenis, string $satuan, int $isHaji, ?string $labId = null, bool $withTrashed = false)
  {
    $query = $withTrashed ? BakuMutu::withTrashed() : BakuMutu::query();
    $query->where('parameter_jenis_klinik_id', $jenis)
      ->where('parameter_satuan_klinik_id', $satuan);

    if ($labId) {
      $query->where('lab_id', $labId);
    }

    return $this->applyIsHajiScope($query, $isHaji);
  }

  private function isReplaceGroupRowValid(array $row): bool
  {
    if (!is_array($row) || $row === []) {
      return false;
    }

    return true;
  }

  /**
   * Hapus permanen seluruh baris grup (termasuk soft-deleted) sebelum insert ulang.
   */
  private function purgeGroupBakuMutuRecords(array $ctx): int
  {
    $idQuery = BakuMutu::withTrashed();
    $this->applyReplaceGroupScope($idQuery, $ctx);
    $ids = $idQuery->pluck('id_baku_mutu')->all();

    if ($ids === []) {
      return 0;
    }

    foreach (array_chunk($ids, 500) as $chunk) {
      BakuMutuDetailParameterKlinik::withTrashed()
        ->whereIn('baku_mutu_id', $chunk)
        ->forceDelete();

      DB::table('tb_baku_mutu_detail_parameter_klinik')
        ->whereIn('baku_mutu_id', $chunk)
        ->delete();

      BakuMutu::withTrashed()
        ->whereIn('id_baku_mutu', $chunk)
        ->forceDelete();
    }

    $remaining = BakuMutu::withTrashed();
    $this->applyReplaceGroupScope($remaining, $ctx);

    if ($remaining->count() > 0) {
      throw new \RuntimeException('Gagal menghapus data lama grup');
    }

    return count($ids);
  }

  private function resolveReplaceGroupContext(Request $request): array
  {
    $satuan = trim((string) $request->input('parameter_satuan_klinik_id', ''));
    $jenis = trim((string) $request->input('parameter_jenis_klinik_id', ''));
    $labId = trim((string) $request->input('lab_id', ''));
    $libraryId = trim((string) $request->input('library_id', ''));
    $unitId = trim((string) $request->input('unit_id', ''));
    $isHaji = (int) $request->input('is_haji', 0);

    $paramSatuan = $satuan !== '' ? ParameterSatuanKlinik::find($satuan) : null;
    if ($jenis === '' && $paramSatuan && $paramSatuan->parameter_jenis_klinik) {
      $jenis = $paramSatuan->parameter_jenis_klinik;
    }

    if ($labId === '' || $libraryId === '' || $unitId === '') {
      $existingQuery = BakuMutu::withTrashed();
      if ($satuan !== '') {
        $this->applySatuanGroupScope($existingQuery, $satuan, $isHaji);
      }
      $existing = $existingQuery->orderByDesc('created_at')
        ->first(['lab_id', 'library_id', 'unit_id', 'parameter_jenis_klinik_id']);

      if ($jenis === '' && $existing && !empty($existing->parameter_jenis_klinik_id)) {
        $jenis = $existing->parameter_jenis_klinik_id;
      }
      if ($labId === '' && $existing) {
        $labId = $existing->lab_id ?? '';
      }
      if ($libraryId === '' && $existing) {
        $libraryId = $existing->library_id ?? '';
      }
      if ($unitId === '' && $existing) {
        $unitId = $existing->unit_id ?? '';
      }
    }

    if ($labId === '') {
      $lab = Laboratorium::where('id_laboratorium', 'bbed2259-2826-4711-b0fc-abdad5aace22')
        ->orWhere('kode_laboratorium', 'KLI')
        ->first();
      $labId = $lab ? $lab->id_laboratorium : '';
    }

    return [
      'jenis' => $jenis,
      'satuan' => $satuan,
      'lab_id' => $labId !== '' ? $labId : null,
      'library_id' => $libraryId !== '' ? $libraryId : null,
      'unit_id' => $unitId !== '' ? $unitId : null,
      'is_haji' => $isHaji,
    ];
  }

  private function sanitizeGroupRowsForInsert(array $rows): array
  {
    return array_values(array_map(function ($row) {
      if (!is_array($row)) {
        return [];
      }
      unset($row['id_baku_mutu']);

      return $row;
    }, $rows));
  }

  private function insertReplaceGroupRow(array $row, array $ctx): int
  {
    $normGender = isset($row['gender_baku_mutu']) && $row['gender_baku_mutu'] !== '' ? $row['gender_baku_mutu'] : null;
    $normUmin = isset($row['minimal_umur_baku_mutu']) && $row['minimal_umur_baku_mutu'] !== '' ? $row['minimal_umur_baku_mutu'] : null;
    $normUmax = isset($row['maksimal_umur_baku_mutu']) && $row['maksimal_umur_baku_mutu'] !== '' ? $row['maksimal_umur_baku_mutu'] : null;
    $normIsKhusus = $this->normalizeIsKhususValue($row['is_khusus_baku_mutu'] ?? 0);
    $normIsNormal = isset($row['is_normal']) && ($row['is_normal'] === 1 || $row['is_normal'] === '1' || $row['is_normal'] === true) ? 1 : 0;
    $normIsMassal = isset($row['is_massal_nilai_di_laporan']) && ($row['is_massal_nilai_di_laporan'] === 1 || $row['is_massal_nilai_di_laporan'] === '1' || $row['is_massal_nilai_di_laporan'] === true) ? 1 : 0;
    $normKesimpulan = isset($row['kesimpulan_baku_mutu']) && $row['kesimpulan_baku_mutu'] !== '' ? $row['kesimpulan_baku_mutu'] : null;

    $normUminFinal = null;
    $normUmaxFinal = null;
    if ((int) $ctx['is_haji'] === 1 && $normIsKhusus === 1) {
      $normUminFinal = null;
      $normUmaxFinal = null;
    } else {
      $normUminFinal = $normUmin;
      $normUmaxFinal = $normUmax;
    }

    if ($normIsKhusus === 0 && (int) $ctx['is_haji'] === 0) {
      if (($normUmin !== null && $normUmin !== '') || ($normUmax !== null && $normUmax !== '')) {
        $normIsKhusus = 1;
        $normUminFinal = $normUmin;
        $normUmaxFinal = $normUmax;
      } else {
        $normGender = null;
        $normUminFinal = null;
        $normUmaxFinal = null;
      }
    }

    $payload = [
      'library_id' => $ctx['library_id'],
      'lab_id' => $ctx['lab_id'],
      'parameter_jenis_klinik_id' => $ctx['jenis'],
      'parameter_satuan_klinik_id' => $ctx['satuan'],
      'is_sub_parameter_satuan_baku_mutu' => 0,
      'is_khusus_baku_mutu' => $normIsKhusus,
      'is_haji' => (int) $ctx['is_haji'],
      'minimal_umur_baku_mutu' => $normUminFinal,
      'maksimal_umur_baku_mutu' => $normUmaxFinal,
      'gender_baku_mutu' => $normGender,
      'kesimpulan_baku_mutu' => $normKesimpulan,
      'is_normal' => $normIsNormal,
      'min' => $row['min'] ?? null,
      'max' => $row['max'] ?? null,
      'equal' => $row['equal'] ?? null,
      'nilai_baku_mutu' => $row['nilai_baku_mutu'] ?? null,
      'is_massal_nilai_di_laporan' => $normIsMassal,
      'unit_id' => $ctx['unit_id'],
    ];

    $savePayload = function (array $p) {
      $model = new BakuMutu();
      $this->applyBakuMutuRowFields($model, $p, true);
      $model->save();
    };

    if ($normIsKhusus === 1 && $normGender === 'A') {
      foreach (['L', 'P'] as $g) {
        $payload['gender_baku_mutu'] = $g;
        $savePayload($payload);
      }

      return 2;
    }

    $savePayload($payload);

    return 1;
  }

  /**
   * Terapkan payload baris baku mutu ke model (create maupun update).
   */
  private function applyBakuMutuRowFields(BakuMutu $model, array $payload, bool $encodeValues = false): void
  {
    $isKhusus = $this->normalizeIsKhususValue($payload['is_khusus_baku_mutu'] ?? 0);

    $model->library_id = $payload['library_id'];
    $model->lab_id = $payload['lab_id'];
    $model->parameter_jenis_klinik_id = $payload['parameter_jenis_klinik_id'];
    $model->parameter_satuan_klinik_id = $payload['parameter_satuan_klinik_id'];
    $model->is_sub_parameter_satuan_baku_mutu = (int)($payload['is_sub_parameter_satuan_baku_mutu'] ?? 0);
    $model->is_khusus_baku_mutu = (string) $isKhusus;
    $model->is_haji = (int)($payload['is_haji'] ?? 0);

    if ($isKhusus === 1) {
      if (isset($payload['is_haji']) && (int)$payload['is_haji'] === 1) {
        $model->minimal_umur_baku_mutu = null;
        $model->maksimal_umur_baku_mutu = null;
      } else {
        $model->minimal_umur_baku_mutu = $payload['minimal_umur_baku_mutu'] ?? null;
        $model->maksimal_umur_baku_mutu = $payload['maksimal_umur_baku_mutu'] ?? null;
      }
      $gender = $payload['gender_baku_mutu'] ?? null;
      $model->gender_baku_mutu = ($gender === '' || $gender === null) ? null : $gender;
    } else {
      $model->minimal_umur_baku_mutu = null;
      $model->maksimal_umur_baku_mutu = null;
      $model->gender_baku_mutu = null;
    }

    $model->kesimpulan_baku_mutu = $payload['kesimpulan_baku_mutu'] ?? null;
    $model->is_normal = !empty($payload['is_normal']) ? 1 : 0;
    $model->min = $this->normalizeNumericField($payload['min'] ?? null);
    $model->max = $this->normalizeNumericField($payload['max'] ?? null);
    $model->unit_id = $payload['unit_id'] ?? null;
    $model->is_massal_nilai_di_laporan = (int)($payload['is_massal_nilai_di_laporan'] ?? 0);

    if ($encodeValues) {
      $model->equal = (isset($payload['equal']) && $payload['equal'] && trim($payload['equal']) !== '')
        ? rubahNilaikeHtml(str_replace(',', '.', $payload['equal']))
        : null;
      $model->nilai_baku_mutu = isset($payload['nilai_baku_mutu']) && $payload['nilai_baku_mutu']
        ? (preg_match('/<[a-zA-Z]/', (string)$payload['nilai_baku_mutu'])
            ? rubahNilaikeHtml($payload['nilai_baku_mutu'])
            : rubahNilaikeHtml(str_replace(',', '.', $payload['nilai_baku_mutu'])))
        : null;
    } else {
      $model->equal = $payload['equal'] ?? null;
      $model->nilai_baku_mutu = $payload['nilai_baku_mutu'] ?? null;
    }
  }

  private function upsertBakuMutuRow(array $payload)
  {
    $model = null;
    if (!empty($payload['id_baku_mutu'])) {
      $model = BakuMutu::find($payload['id_baku_mutu']);
    }
    if (!$model) {
      $model = new BakuMutu();
    }

    $this->applyBakuMutuRowFields($model, $payload, false);
    $model->save();
  }

  private function createBakuMutuRow(array $payload): BakuMutu
  {
    $model = new BakuMutu();
    $this->applyBakuMutuRowFields($model, $payload, false);
    $model->save();
    return $model;
  }

  public function rules($request)
  {
    // Untuk form haji, parameter_jenis_klinik_id tidak required (akan di-set otomatis dari parameter satuan)
    $is_haji = isset($request['is_haji']) && $request['is_haji'] == 1;
    
    $rule = [
      'parameter_satuan_klinik_id' => 'required',
      'library_id' => 'required',
    ];
    
    // parameter_jenis_klinik_id hanya required jika bukan haji
    if (!$is_haji) {
      $rule['parameter_jenis_klinik_id'] = 'required';
    }

    $pesan = [
      'parameter_jenis_klinik_id.required' => 'Parameter jenis klinik tidak boleh kosong!',
      'parameter_satuan_klinik_id.required' => 'Parameter satuan klinik tidak boleh kosong!',
      'library_id.required' => 'Acuan baku mutu tidak boleh kosong!',
    ];

    return Validator::make($request, $rule, $pesan);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // #1 attempt
    /* $user = Auth()->user();
    $level = $user->getlevel->level;

    if ($level == "elits-dev" || $level == "admin" || $level == "LAB") {
      $get_lab = Laboratorium::where('id_laboratorium', 'bbed2259-2826-4711-b0fc-abdad5aace22')
        ->orwhere('kode_laboratorium', 'KLI')
        ->first();

      $lab_link = strtolower($get_lab->nama_laboratorium);
      $lab = $get_lab->nama_laboratorium;

      return view('masterweb::module.admin.laboratorium.baku-mutu-klinik.list', compact('lab_link', 'lab'));
    } else {
      return abort(404);
    } */

    // #2 attempt
    $get_lab = Laboratorium::where('id_laboratorium', 'bbed2259-2826-4711-b0fc-abdad5aace22')
      ->orwhere('kode_laboratorium', 'KLI')
      ->first();

    $lab_link = strtolower($get_lab->nama_laboratorium);
    $lab = $get_lab->nama_laboratorium;

    return view('masterweb::module.admin.laboratorium.baku-mutu-klinik.list', compact('lab_link', 'lab'));
  }

  public function data_baku_mutu_klinik(Request $request)
  {
    $get_lab = Laboratorium::where('id_laboratorium', 'bbed2259-2826-4711-b0fc-abdad5aace22')
      ->orwhere('kode_laboratorium', 'KLI')
      ->first();

    $group = $request->get('group');
    $is_haji = $request->get('is_haji', 0); // Filter berdasarkan is_haji

    if ($group) {
      // grouped by jenis + parameter satuan
      $datas = BakuMutu::where('lab_id', $get_lab->id_laboratorium)
        ->where('is_haji', (int)$is_haji)
        ->whereHas('laboratorium', function ($q) { $q->whereNull('deleted_at'); })
        ->get()
        ->groupBy(function($bm){ return $bm->parameter_jenis_klinik_id.'|'.$bm->parameter_satuan_klinik_id; })
        ->map(function($groupItems){
          // urutkan item dalam group dari min terkecil ke max terbesar
          $groupItems = $groupItems->sortBy(function($g){
            $toNum = function($v){
              if ($v === null || $v === '') return 999999999; // besar agar null di akhir
              $v = preg_replace('/[^0-9\,\.-]/', '', $v);
              $v = str_replace(',', '.', $v);
              return (float) $v;
            };
            $min = $toNum($g->min ?? null);
            $max = $toNum($g->max ?? null);
            return sprintf('%020.6f-%020.6f', $min, $max);
          });
          $first = $groupItems->first();
          // gabungkan nilai baku mutu + kesimpulan + penanda normal
          $nilaiParts = [];
          $isMassal = $groupItems->contains(fn($bm) => (int)($bm->is_massal_nilai_di_laporan ?? 0) === 1);
          // Jika massal: ambil hanya satu nilai (pertama yang tidak kosong)
          $itemsForNilai = $isMassal
            ? $groupItems->filter(fn($bm) => !empty($bm->nilai_baku_mutu))->take(1)
            : $groupItems;
          foreach ($itemsForNilai as $g) {
            $line = '';
            if ($g->nilai_baku_mutu) {
              $line .= nilaiBakuMutuForDisplay($g->nilai_baku_mutu);
            }
            if ($g->kesimpulan_baku_mutu) {
              $line .= '<br><small>' . nilaiBakuMutuForDisplay($g->kesimpulan_baku_mutu) . '</small>';
            }
            if (isset($g->is_normal) && (int)$g->is_normal === 1) {
              $line .= ' <span class="badge badge-success badge-pill" title="Batas normal">&#10003; Normal</span>';
            }
            if ($line !== '') {
              $nilaiParts[] = $line;
            }
          }
          $nilaiList = count($nilaiParts) ? implode('<hr>', $nilaiParts) : '-';
          $detail = [];
          foreach ($groupItems as $g) {
            if (($g->minimal_umur_baku_mutu != null || $g->maksimal_umur_baku_mutu != null) || $g->gender_baku_mutu != null) {
              $gender = $g->gender_baku_mutu == 'L' ? 'Laki-laki' : ($g->gender_baku_mutu == 'P' ? 'Perempuan' : '-');
              $detail[] = 'Umur: '.($g->minimal_umur_baku_mutu ?? '-').'-'.($g->maksimal_umur_baku_mutu ?? '-').' | JK: '.$gender.((isset($g->is_normal) && (int)$g->is_normal === 1)? ' | <span class="badge badge-success badge-pill" title="Batas normal">Normal</span>' : '');
            }
          }
          $first->nilai_baku_mutu_grouped = $nilaiList ?: '-';
          $first->is_massal_nilai_di_laporan_grouped = $isMassal ? 1 : 0;
          $first->detail_grouped = count($detail) ? implode('<br>', $detail) : '-';
          // status grouped: jika ada satu saja specific, tampilkan Specific, selain itu General
          $isRowSpecific = function ($bm) {
            return ($bm->is_khusus_baku_mutu == 1 || $bm->is_khusus_baku_mutu === '1' || $bm->is_khusus_baku_mutu === true);
          };
          $hasSpecific = $groupItems->contains($isRowSpecific);
          $first->is_khusus_baku_mutu_grouped = $hasSpecific ? 'Specific' : 'General';
          return $first;
        })->values();
    } else {
      $datas = BakuMutu::where('lab_id', $get_lab->id_laboratorium)
        ->where('is_haji', (int)$is_haji)
        ->orderBy('created_at', 'desc')
        ->whereHas('laboratorium', function ($query) {
          $query->whereNull('deleted_at');
        })
        ->get();
    }

    return Datatables::of($datas)
      ->addColumn('action', function ($data) use ($group) {
        if ($group) {
          // Untuk haji dan non-haji, tampilkan "Edit massal grup"
          $jenisNama = htmlspecialchars($data->parameterjenisklinik->name_parameter_jenis_klinik ?? '-', ENT_QUOTES, 'UTF-8');
          $satuanNama = htmlspecialchars($data->parametersatuanklinik->name_parameter_satuan_klinik ?? '-', ENT_QUOTES, 'UTF-8');
          $libraryText = htmlspecialchars($data->library->title_library ?? '', ENT_QUOTES, 'UTF-8');
          $unitText = htmlspecialchars($data->unit->shortname_unit ?? '', ENT_QUOTES, 'UTF-8');
          $editGroupBtn = '<button type="button" class="dropdown-item btn-edit-group" data-jenis="' . $data->parameter_jenis_klinik_id . '" data-satuan="' . $data->parameter_satuan_klinik_id . '" data-lab="' . $data->lab_id . '" data-unit="' . ($data->unit_id ?? '') . '" data-library="' . $data->library_id . '" data-haji="' . ($data->is_haji ?? 0) . '" data-jenis-nama="' . $jenisNama . '" data-satuan-nama="' . $satuanNama . '" data-library-text="' . $libraryText . '" data-unit-text="' . $unitText . '">Edit massal grup</button>';
          $button = '<div class="dropdown show m-1">
              <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</a>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">' . $editGroupBtn . '</div></div>';
          return $button;
        }
        $detailButton = '<a class="dropdown-item" href="' . route('elits-baku-mutu-klinik.show', $data->id_baku_mutu) . '" title="Detail">Detail</a> ';

        $editButton = '<a href="' . route('elits-baku-mutu-klinik.edit', $data->id_baku_mutu) . '" class="dropdown-item" title="Edit">Edit</a> ';

        if ($data->parametersatuanklinik->name_parameter_satuan_klinik) {
          $name_parameter_satuan = $data->parametersatuanklinik->name_parameter_satuan_klinik;
        } else {
          $name_parameter_satuan = '-';
        }


        $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_baku_mutu   . '" data-nama="' . $name_parameter_satuan . '" title="Hapus">Hapus</a> ';

        $button = '<div class="dropdown show m-1">
                            <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Aksi
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $detailButton . '

                                ' . $editButton . '

                                ' . $deleteButton . '
                            </div>
                        </div>';

        return $button;
      })
      ->addColumn('jenis_parameter', function ($data) {
        if ($data->parameterjenisklinik->name_parameter_jenis_klinik) {
          $jenis_parameter = $data->parameterjenisklinik->name_parameter_jenis_klinik;
        } else {
          $jenis_parameter = '-';
        }

        return $jenis_parameter;
      })
      ->addColumn('parameter_satuan', function ($data) {
        if ($data->parametersatuanklinik->name_parameter_satuan_klinik) {
          $parameter_satuan = $data->parametersatuanklinik->name_parameter_satuan_klinik;
        } else {
          $parameter_satuan = '-';
        }

        return $parameter_satuan;
      })
      ->addColumn('library', function ($data) {
        if ($data->library->title_library) {
          $library = $data->library->title_library;
        } else {
          $library = '-';
        }

        return $library;
      })
      ->addColumn('nilai_baku_mutu', function ($data) use ($group) {
        if ($group && isset($data->nilai_baku_mutu_grouped)) {
          $badge = ($data->is_massal_nilai_di_laporan_grouped ?? 0)
            ? ' <span class="badge badge-info badge-pill" title="Satu nilai untuk seluruh grup"><i class="fa fa-clone mr-1"></i>Massal</span>'
            : '';
          return ($data->nilai_baku_mutu_grouped ?: '-') . $badge;
        }
        if ($data->nilai_baku_mutu) {
          $line = nilaiBakuMutuForDisplay($data->nilai_baku_mutu);
          if ($data->kesimpulan_baku_mutu) {
            $line .= '<br><small>' . nilaiBakuMutuForDisplay($data->kesimpulan_baku_mutu) . '</small>';
          }
          if (isset($data->is_normal) && (int)$data->is_normal === 1) {
            $line .= ' <span class="badge badge-success">Normal</span>';
          }
          return $line;
        }
        return '-';
      })
      ->addColumn('is_khusus_baku_mutu', function ($data) use ($group) {
        if ($group && isset($data->is_khusus_baku_mutu_grouped)) {
          return $data->is_khusus_baku_mutu_grouped;
        }
        return ($data->is_khusus_baku_mutu == 1 || $data->is_khusus_baku_mutu === '1') ? 'Specific' : 'General';
      })
      ->addColumn('is_haji', function ($data) {
        return ($data->is_haji == 1) ? '<span class="badge badge-info">Haji</span>' : '<span class="badge badge-secondary">Non-Haji</span>';
      })
      ->addColumn('detail_data_khusus', function ($data) use ($group) {
        if ($group && isset($data->detail_grouped)) {
          return $data->detail_grouped ?: '-';
        }
        if (($data->minimal_umur_baku_mutu != null || $data->maksimal_umur_baku_mutu != null) && $data->gender_baku_mutu != null) {
          if ($data->gender_baku_mutu == "L") {
            $gender = "Laki-laki";
          } else {
            $gender = "Perempuan";
          }

          $status = "Umur: " . $data->minimal_umur_baku_mutu . "-" . $data->maksimal_umur_baku_mutu . "<br>" . "Jenis kelamin: " . $gender;
        } else if (($data->minimal_umur_baku_mutu == null || $data->maksimal_umur_baku_mutu == null) && $data->gender_baku_mutu != null) {
          if ($data->gender_baku_mutu == "L") {
            $gender = "Laki-laki";
          } else {
            $gender = "Perempuan";
          }

          $status = "Jenis kelamin: " . $gender;
        } else {
          $status = "-";
        }

        return $status;
      })
      ->rawColumns(['action', 'jenis_parameter', 'parameter_satuan', 'library', 'nilai_baku_mutu', 'is_khusus_baku_mutu', 'detail_data_khusus', 'is_haji'])
      ->addIndexColumn() //increment
      ->make(true);
  }

  // update massal dalam satu grup (jenis + parameter_satuan)
  public function updateGroup(Request $request)
  {
    $this->middleware('auth');
    $request->validate([
      'parameter_jenis_klinik_id' => 'required',
      'parameter_satuan_klinik_id' => 'required',
    ]);

    $jenis = $request->post('parameter_jenis_klinik_id');
    $satuan = $request->post('parameter_satuan_klinik_id');

    $updates = [
      'nilai_baku_mutu' => $request->post('nilai_baku_mutu') ? rubahNilaikeHtml(str_replace(",", ".", $request->post('nilai_baku_mutu'))) : null,
      'min' => $request->post('min'),
      'max' => $request->post('max'),
      'equal' => ($request->post('equal') && trim($request->post('equal')) !== '') ? rubahNilaikeHtml(str_replace(",", ".", $request->post('equal'))) : null,
      'kesimpulan_baku_mutu' => $request->post('kesimpulan_baku_mutu'),
    ];

    // bersihkan agar tidak overwrite ke null tanpa sengaja
    $updates = array_filter($updates, function($v){ return !is_null($v); });

    $affected = BakuMutu::where('parameter_jenis_klinik_id', $jenis)
      ->where('parameter_satuan_klinik_id', $satuan)
      ->update($updates);

    return response()->json(['status' => true, 'updated' => $affected]);
  }

  // Get data satuan untuk edit popup satuan saja (khusus haji)
  public function getSatuan(Request $request)
  {
    $jenis = $request->post('parameter_jenis_klinik_id');
    $satuan = $request->post('parameter_satuan_klinik_id');
    
    // Ambil data pertama dari grup untuk mendapatkan library_id, unit_id, dll
    $firstData = BakuMutu::with(['library', 'unit'])
      ->where('parameter_jenis_klinik_id', $jenis)
      ->where('parameter_satuan_klinik_id', $satuan)
      ->where('is_sub_parameter_satuan_baku_mutu', 0)
      ->where('is_haji', 1) // Hanya untuk haji
      ->first();
    
    if (!$firstData) {
      return response()->json(['status' => false, 'pesan' => 'Data tidak ditemukan'], 200);
    }
    
    return response()->json([
      'status' => true,
      'data' => [
        'library_id' => $firstData->library_id,
        'library_text' => $firstData->library ? $firstData->library->title_library : '',
        'unit_id' => $firstData->unit_id,
        'unit_text' => $firstData->unit ? $firstData->unit->shortname_unit : '',
        'min' => $firstData->min ? rubahNilaikeForm($firstData->min) : '',
        'max' => $firstData->max ? rubahNilaikeForm($firstData->max) : '',
        'equal' => $firstData->equal ? rubahNilaikeForm($firstData->equal) : '',
        'nilai_baku_mutu' => $firstData->nilai_baku_mutu ? rubahNilaikeForm($firstData->nilai_baku_mutu) : '',
      ]
    ]);
  }

  // Update satuan saja (library, unit, min, max, equal, nilai_baku_mutu) - khusus haji
  public function updateSatuan(Request $request)
  {
    $this->middleware('auth');
    $request->validate([
      'parameter_jenis_klinik_id' => 'required',
      'parameter_satuan_klinik_id' => 'required',
      'library_id' => 'required',
      'unit_id' => 'required',
    ]);

    $jenis = $request->post('parameter_jenis_klinik_id');
    $satuan = $request->post('parameter_satuan_klinik_id');

    $updates = [
      'library_id' => $request->post('library_id'),
      'unit_id' => $request->post('unit_id'),
      'min' => $request->post('min') ? str_replace(",", ".", $request->post('min')) : null,
      'max' => $request->post('max') ? str_replace(",", ".", $request->post('max')) : null,
      'equal' => ($request->post('equal') && trim($request->post('equal')) !== '') ? rubahNilaikeHtml(str_replace(",", ".", $request->post('equal'))) : null,
      'nilai_baku_mutu' => $request->post('nilai_baku_mutu') ? rubahNilaikeHtml(str_replace(",", ".", $request->post('nilai_baku_mutu'))) : null,
    ];

    $affected = BakuMutu::where('parameter_jenis_klinik_id', $jenis)
      ->where('parameter_satuan_klinik_id', $satuan)
      ->where('is_haji', 1) // Hanya update untuk haji
      ->update($updates);

    return response()->json(['status' => true, 'updated' => $affected, 'pesan' => 'Data satuan berhasil diperbarui']);
  }

  private function applyMassalNilaiToRows(array $rows, Request $request): array
  {
    $isMassal = (int) $request->input('is_massal_nilai_di_laporan', 0) === 1;
    if (!$isMassal) {
      $isMassal = collect($rows)->contains(function ($row) {
        return !empty($row['is_massal_nilai_di_laporan'])
          && ($row['is_massal_nilai_di_laporan'] === 1
            || $row['is_massal_nilai_di_laporan'] === '1'
            || $row['is_massal_nilai_di_laporan'] === true);
      });
    }

    if (!$isMassal) {
      return $rows;
    }

    $sharedNilai = trim((string) $request->input('shared_nilai_baku_mutu', ''));
    if ($sharedNilai === '') {
      foreach ($rows as $row) {
        if (!empty($row['nilai_baku_mutu'])) {
          $sharedNilai = $row['nilai_baku_mutu'];
          break;
        }
      }
    }

    foreach ($rows as $index => $row) {
      $rows[$index]['is_massal_nilai_di_laporan'] = 1;
      if ($sharedNilai !== '') {
        $rows[$index]['nilai_baku_mutu'] = $sharedNilai;
      }
    }

    return $rows;
  }

  // Ambil semua item dalam satu grup untuk diedit massal di UI seperti tab massal
  public function getGroup(Request $request)
  {
    $ctx = $this->resolveReplaceGroupContext($request);

    if ($ctx['satuan'] === '') {
      return response()->json(['status' => false, 'items' => [], 'pesan' => 'Parameter tidak valid']);
    }

    $rawItems = BakuMutu::query();
    $this->applyReplaceGroupScope($rawItems, $ctx);
    $rawItems = $rawItems
      ->orderByRaw('CAST(min AS DECIMAL(12,4)) ASC')
      ->orderByRaw('CAST(max AS DECIMAL(12,4)) ASC')
      ->orderBy('kesimpulan_baku_mutu')
      ->get(['id_baku_mutu','min','max','equal','nilai_baku_mutu','is_massal_nilai_di_laporan','gender_baku_mutu','minimal_umur_baku_mutu','maksimal_umur_baku_mutu','kesimpulan_baku_mutu','is_normal','is_khusus_baku_mutu','is_haji']);

    $is_haji = (int) $ctx['is_haji'];

    $isMassal = $rawItems->contains(function ($it) {
      return (int) ($it->is_massal_nilai_di_laporan ?? 0) === 1;
    });

    $sharedNilai = '';
    if ($isMassal) {
      foreach ($rawItems as $it) {
        if (!empty($it->nilai_baku_mutu)) {
          $sharedNilai = rubahNilaikeForm($it->nilai_baku_mutu);
          break;
        }
      }
    }

    $items = $rawItems->map(function ($it) use ($isMassal) {
      $it->is_khusus_baku_mutu = $this->normalizeIsKhususValue($it->is_khusus_baku_mutu ?? 0) ? '1' : '0';
      $it->is_massal_nilai_di_laporan = (int) ($it->is_massal_nilai_di_laporan ?? 0);
      if ($isMassal) {
        $it->nilai_baku_mutu = '';
      } else {
        $it->nilai_baku_mutu = rubahNilaikeForm($it->nilai_baku_mutu);
      }
      $it->equal = $it->equal ? rubahNilaikeForm($it->equal) : '';
      return $it;
    })->values();

    return response()->json([
      'status' => true,
      'items' => $items,
      'is_haji' => $is_haji,
      'is_massal_nilai_di_laporan' => $isMassal ? 1 : 0,
      'shared_nilai_baku_mutu' => $sharedNilai,
    ]);
  }

  private function syncPermohonanBakuMutuFromDate(array $ctx, $bakuMutuItems, string $fromDate): int
  {
    if (!BakuMutuPermohonanKlinikHelper::hasSnapshotColumns()) {
      throw new \RuntimeException('Kolom snapshot baku mutu permohonan belum tersedia. Jalankan migrasi terlebih dahulu.');
    }

    $from = Carbon::parse($fromDate)->startOfDay();
    $isHaji = (int) $ctx['is_haji'];

    $groupUnitId = $ctx['unit_id'] ?? null;
    if (empty($groupUnitId) && $bakuMutuItems instanceof \Illuminate\Support\Collection && $bakuMutuItems->isNotEmpty()) {
      $groupUnitId = $bakuMutuItems->first()->unit_id ?? null;
    }

    $params = PermohonanUjiParameterKlinik::query()
      ->where('parameter_satuan_klinik', $ctx['satuan'])
      ->whereHas('permohonanujiklinik', function ($q) use ($from, $isHaji) {
        $q->where('created_at', '>=', $from);
        if ($isHaji === 1) {
          $q->where('is_haji', 1);
        } else {
          $q->where(function ($qq) {
            $qq->where('is_haji', 0)->orWhereNull('is_haji');
          });
        }
      })
      ->with(['permohonanujiklinik.pasien'])
      ->get();

    $synced = 0;
    foreach ($params as $param) {
      $permohonan = $param->permohonanujiklinik;
      $pasien = optional($permohonan)->pasien;
      $gender = BakuMutuPermohonanKlinikHelper::normalizePasienGender($pasien->gender_pasien ?? null);
      $umur = null;
      if ($permohonan && $permohonan->umurtahun_pasien_permohonan_uji_klinik !== null && $permohonan->umurtahun_pasien_permohonan_uji_klinik !== '') {
        $umur = (int) $permohonan->umurtahun_pasien_permohonan_uji_klinik;
      } elseif (isset($pasien->umurtahun_pasien) && $pasien->umurtahun_pasien !== '' && $pasien->umurtahun_pasien !== null) {
        $umur = (int) $pasien->umurtahun_pasien;
      } elseif (!empty($pasien->tgllahir_pasien)) {
        $umur = (int) \Carbon\Carbon::parse($pasien->tgllahir_pasien)->age;
      }

      $primary = BakuMutuPermohonanKlinikHelper::resolveForPasien($bakuMutuItems, $gender, $umur);
      if (!$primary) {
        continue;
      }

      $allNormal = BakuMutuPermohonanKlinikHelper::resolveAllNormalForPasien($bakuMutuItems, $gender, $umur);
      BakuMutuPermohonanKlinikHelper::applySnapshotToParameter($param, $primary, $allNormal, $bakuMutuItems);
      if (!empty($groupUnitId)) {
        $param->satuan_permohonan_uji_parameter_klinik = $groupUnitId;
      }
      $param->save();
      $synced++;
    }

    return $synced;
  }

  // Simpan massal grup: hapus SEMUA data satuan ini, lalu insert baris baru dari tabel (bukan update/replace per id).
  public function replaceGroup(Request $request)
  {
    $ctx = $this->resolveReplaceGroupContext($request);
    $rows = $this->sanitizeGroupRowsForInsert($this->parseReplaceGroupRows($request));

    if ($ctx['satuan'] === '') {
      return response()->json(['status' => false, 'pesan' => 'Parameter satuan tidak valid'], 200);
    }

    if ($ctx['jenis'] === '') {
      return response()->json(['status' => false, 'pesan' => 'Parameter jenis tidak valid'], 200);
    }

    $validRows = array_values(array_filter($rows, function ($row) {
      return $this->isReplaceGroupRowValid($row);
    }));

    if (count($validRows) === 0) {
      return response()->json(['status' => false, 'pesan' => 'Tidak ada data baris untuk disimpan'], 200);
    }

    $sentCount = count($rows);
    $validRows = $this->applyMassalNilaiToRows($validRows, $request);

    DB::beginTransaction();
    try {
      $lockQuery = BakuMutu::query();
      $this->applyReplaceGroupScope($lockQuery, $ctx);
      $lockQuery->lockForUpdate()->pluck('id_baku_mutu');

      // 1) Hapus permanen seluruh data lama untuk satuan (+ is_haji)
      $deletedCount = $this->purgeGroupBakuMutuRecords($ctx);

      $afterPurgeQuery = BakuMutu::query();
      $this->applyReplaceGroupScope($afterPurgeQuery, $ctx);
      if ($afterPurgeQuery->count() !== 0) {
        throw new \RuntimeException('Data lama belum terhapus semua');
      }

      // 2) Insert baris baru persis dari payload (selalu record baru, tanpa id lama)
      $createdCount = 0;
      foreach ($validRows as $row) {
        $createdCount += $this->insertReplaceGroupRow($row, $ctx);
      }

      $itemsQuery = BakuMutu::query();
      $this->applyReplaceGroupScope($itemsQuery, $ctx);
      $items = $itemsQuery
        ->orderByRaw('CAST(min AS DECIMAL(12,4)) ASC')
        ->orderByRaw('CAST(max AS DECIMAL(12,4)) ASC')
        ->orderBy('kesimpulan_baku_mutu')
        ->get(['id_baku_mutu','unit_id','min','max','equal','nilai_baku_mutu','gender_baku_mutu','minimal_umur_baku_mutu','maksimal_umur_baku_mutu','kesimpulan_baku_mutu','is_normal','is_khusus_baku_mutu']);

      if ($items->count() !== $createdCount) {
        throw new \RuntimeException('Jumlah data tersimpan tidak sesuai');
      }

      $syncedPermohonan = 0;
      $syncPermohonan = (int) $request->input('sync_permohonan', 0) === 1;
      $syncFromDate = trim((string) $request->input('sync_permohonan_from_date', ''));
      if ($syncPermohonan) {
        if ($syncFromDate === '') {
          throw new \RuntimeException('Tanggal mulai penyesuaian permohonan wajib diisi');
        }
        $syncedPermohonan = $this->syncPermohonanBakuMutuFromDate($ctx, $items, $syncFromDate);
      }

      DB::commit();

      return response()->json([
        'status' => true,
        'items' => $items,
        'deleted' => $deletedCount,
        'created' => $createdCount,
        'sent' => $sentCount,
        'synced_permohonan' => $syncedPermohonan,
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('replaceGroup failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
      return response()->json([
        'status' => false,
        'pesan' => 'Gagal replace group: ' . $e->getMessage(),
      ]);
    }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create(Request $request)
  {
    //get auth user
    $user = Auth()->user();
    $level = $user->getlevel->level;

    $get_lab = Laboratorium::where('id_laboratorium', 'bbed2259-2826-4711-b0fc-abdad5aace22')
      ->orwhere('kode_laboratorium', 'KLI')
      ->first();

    $lab_link = strtolower($get_lab->nama_laboratorium);
    $lab = $get_lab->nama_laboratorium;
    
    // Cek apakah ini untuk baku mutu haji
    $is_haji = $request->get('type') == 'haji' ? 1 : 0;

    return view('masterweb::module.admin.laboratorium.baku-mutu-klinik.add', compact('get_lab', 'lab_link', 'lab', 'is_haji'));
  }

  /**
   * Show the form for creating a new haji baku mutu resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function createHaji()
  {
    $get_lab = Laboratorium::where('id_laboratorium', 'bbed2259-2826-4711-b0fc-abdad5aace22')
      ->orwhere('kode_laboratorium', 'KLI')
      ->first();

    $lab_link = strtolower($get_lab->nama_laboratorium);
    $lab = $get_lab->nama_laboratorium;

    return view('masterweb::module.admin.laboratorium.baku-mutu-klinik.add-haji', compact('get_lab', 'lab_link', 'lab'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {

    // Untuk form haji, jika parameter_jenis_klinik_id kosong, ambil dari parameter satuan
    if ($request->has('is_haji') && $request->is_haji == 1 && empty($request->parameter_jenis_klinik_id) && !empty($request->parameter_satuan_klinik_id)) {
      $paramSatuan = \Smt\Masterweb\Models\ParameterSatuanKlinik::find($request->parameter_satuan_klinik_id);
      if ($paramSatuan && $paramSatuan->parameter_jenis_klinik) {
        $request->merge(['parameter_jenis_klinik_id' => $paramSatuan->parameter_jenis_klinik]);
      }
    }
    
    $validator = $this->rules($request->all());
   

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {



     // Handle bulk rows (input massal) bila ada dan berisi; kalau kosong, lanjut single input
      if ($request->has('bulk_rows')) {
     
        $bulkRows = json_decode($request->bulk_rows, true);

        if ($bulkRows === null && json_last_error() !== JSON_ERROR_NONE) {
          return response()->json(['status' => false, 'pesan' => 'Format bulk input tidak valid'], 200);
        }
        if (is_array($bulkRows) && count($bulkRows) > 0) {
          DB::beginTransaction();

          foreach ($bulkRows as $row) {
            $payload = $request->all();
            // override per row
            $payload['is_sub_parameter_satuan_baku_mutu'] = 0; // bulk hanya untuk non-sub saat ini
            $payload['is_khusus_baku_mutu'] = $row['is_khusus_baku_mutu'] ?? '0';
            $payload['is_haji'] = (int)($row['is_haji'] ?? ($request->is_haji ?? 0));
            $payload['gender_baku_mutu'] = $row['gender_baku_mutu'] ?? null;
            // Untuk haji specific, tidak ada umur
            if (isset($payload['is_haji']) && $payload['is_haji'] == 1 && isset($payload['is_khusus_baku_mutu']) && $payload['is_khusus_baku_mutu'] == '1') {
              $payload['minimal_umur_baku_mutu'] = null;
              $payload['maksimal_umur_baku_mutu'] = null;
            } else {
              $payload['minimal_umur_baku_mutu'] = $row['minimal_umur_baku_mutu'] ?? null;
              $payload['maksimal_umur_baku_mutu'] = $row['maksimal_umur_baku_mutu'] ?? null;
            }
            $payload['kesimpulan_baku_mutu'] = $row['kesimpulan_baku_mutu'] ?? null;
            $payload['nilai_baku_mutu'] = isset($row['nilai_baku_mutu']) && $row['nilai_baku_mutu'] ? rubahNilaikeHtml(str_replace(",", ".", $row['nilai_baku_mutu'])) : null;
            $payload['min'] = $row['min'] ?? null;
            $payload['max'] = $row['max'] ?? null;
            $payload['equal'] = (isset($row['equal']) && $row['equal'] && trim($row['equal']) !== '') ? rubahNilaikeHtml(str_replace(",", ".", $row['equal'])) : null;
            $payload['is_normal'] = isset($row['is_normal']) && $row['is_normal'] ? 1 : 0;

            // skip empty
            if ($payload['nilai_baku_mutu'] === null && $payload['min'] === null && $payload['max'] === null && $payload['equal'] === null) {
              continue;
            }

            // Jika Specific dan gender = 'A' (semua gender), buat dua record L & P
            if (($payload['is_khusus_baku_mutu'] == "1") && (isset($payload['gender_baku_mutu']) && $payload['gender_baku_mutu'] === 'A')) {
              foreach (['L','P'] as $g) {
                $payloadCopy = $payload;
                $payloadCopy['gender_baku_mutu'] = $g;
                $this->storeCreateBakuMutu($payloadCopy);
              }
            } else {
              $this->storeCreateBakuMutu($payload);
            }
          }

          DB::commit();
          return response()->json(['status' => true, 'pesan' => 'Data baku mutu klinik (massal) berhasil disimpan!'], 200);
        }
      }

    
   
      // Validasi ringan untuk input tunggal (non-bulk)
      if ((int)($request->post('is_sub_parameter_satuan_baku_mutu') ?? 0) === 0) {
        $hasMin = $request->filled('min') && $request->post('min') !== '';
        $hasMax = $request->filled('max') && $request->post('max') !== '';
        $hasEqual = $request->filled('equal') && $request->post('equal') !== '';
        $hasNilai = $request->filled('nilai_baku_mutu') && $request->post('nilai_baku_mutu') !== '';
        // dd($request->post('is_sub_parameter_satuan_baku_mutu'));
        if (!$hasMin && !$hasMax && !$hasEqual && !$hasNilai) {
          return response()->json(['status' => false, 'pesan' => 'Isi salah satu dari Min, Max, Equal, atau Nilai di Laporan'], 200);
        }
      }
      
      // Handle 2 inputan jika parameter adalah haji (ada field dengan suffix _haji)
      $hasHajiForm = false;
      $hajiFields = [];
      foreach ($request->all() as $key => $value) {
        if (strpos($key, '_haji') !== false && $key !== 'is_haji_haji') {
          $hasHajiForm = true;
          $originalKey = str_replace('_haji', '', $key);
          $hajiFields[$originalKey] = $value;
        }
      }
      
      DB::beginTransaction();
      try {

        // Simpan baku mutu non-haji (default)
        $res = $this->storeCreateBakuMutu($request->all());
      
        if ($res instanceof \Illuminate\Http\JsonResponse) { 
          DB::rollBack();
          return $res; 
        }
        
        // Pastikan $res bukan null
        if ($res === null || $res === false) {
          DB::rollBack();
          return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan data baku mutu'], 200);
        }
        
        // Jika ada form haji, simpan juga baku mutu haji
        if ($hasHajiForm && isset($request->is_haji_haji) && $request->is_haji_haji == '1') {
          $hajiRequest = $request->all();
          // Replace field dengan suffix _haji ke field normal
          foreach ($hajiFields as $key => $value) {
            $hajiRequest[$key] = $value;
          }
          $hajiRequest['is_haji'] = 1;
          
          $resHaji = $this->storeCreateBakuMutu($hajiRequest);
          if ($resHaji instanceof \Illuminate\Http\JsonResponse) { 
            DB::rollBack();
            return $resHaji; 
          }
        }
        
        DB::commit();
        return response()->json(['status' => true, 'pesan' => 'Data baku mutu klinik berhasil disimpan!'], 200);
      } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan data: ' . $e->getMessage()], 200);
      }
    }
  }

  private function storeCreateBakuMutu($request)
  {
    // Jangan begin transaction di sini karena sudah ada di method store()
    // Transaction di-handle di method store() yang memanggil method ini

    try {
      // Convert to array if it's a Request object
      $req = is_array($request) ? $request : $request->all();
      
      $id_parameter = $req['parameter_satuan_klinik_id'];
      
      // Jika parameter_jenis_klinik_id kosong, ambil dari parameter satuan
      if (empty($req['parameter_jenis_klinik_id']) && !empty($req['parameter_satuan_klinik_id'])) {
        $paramSatuan = \Smt\Masterweb\Models\ParameterSatuanKlinik::find($req['parameter_satuan_klinik_id']);
        if ($paramSatuan && $paramSatuan->parameter_jenis_klinik) {
          $req['parameter_jenis_klinik_id'] = $paramSatuan->parameter_jenis_klinik;
        }
      }
      
      // Update $request dengan nilai yang sudah diperbaiki
      $request = $req;

      // Fungsi kecil untuk upsert satu baris (tanpa gender 'A')
      $saveOne = function(array $req) use ($id_parameter) {
        if (!empty($req['id_baku_mutu'])) {
          $model = BakuMutu::find($req['id_baku_mutu']) ?: new BakuMutu();
        } else {
          $model = $this->findBakuMutuBySignature($req)->first() ?: new BakuMutu();
        }

        $this->applyBakuMutuRowFields($model, $req, true);

        if ((int)($req['is_sub_parameter_satuan_baku_mutu'] ?? 0) == 0) {
          $this->updateBakuMutuPermohonanUji($req, $id_parameter);
        }
        // is_option
        $model->is_option = isset($req['is_option']) && $req['is_option'] ? 1 : 0;
        $model->option = $model->is_option ? ($req['option'] ?? null) : null;

        $model->save();
        return $model;
      };


      // Jika sub-satuan, cukup simpan satu model utama (tanpa duplikasi)
      $mainModel = null;
      if ($req['is_sub_parameter_satuan_baku_mutu'] == 1) {
        $mainModel = $saveOne($req);
        $simpan = true;
      } else {
        // Handle gender 'A' -> update/create L dan P; selain itu single
        if ($req['is_khusus_baku_mutu'] == "1" && isset($req['gender_baku_mutu']) && $req['gender_baku_mutu'] === 'A') {
          $reqL = $req; $reqL['gender_baku_mutu'] = 'L';
          $reqP = $req; $reqP['gender_baku_mutu'] = 'P';
          $saveOne($reqL);
          $mainModel = $saveOne($reqP); // ambil salah satu untuk referensi id bila diperlukan
          $simpan = true;
        } else {
          $mainModel = $saveOne($req);
          $simpan = true;
        }
      }


      // jika dibaku mutu untuk parameter memiliki sub satuan
      if ($req['is_sub_parameter_satuan_baku_mutu'] == 1 && $mainModel) {
        if ($req['parameter_sub_satuan_baku_mutu_detail_parameter_klinik'] !== null) {
          $count_sub_parameter_satuan = count($req['parameter_sub_satuan_baku_mutu_detail_parameter_klinik']);

          for ($i = 0; $i < $count_sub_parameter_satuan; $i++) {
            $post_sub_parameter = new BakuMutuDetailParameterKlinik();
            $post_sub_parameter->baku_mutu_id = is_object($mainModel) ? $mainModel->id_baku_mutu : $mainModel['id_baku_mutu'];
            $post_sub_parameter->parameter_sub_satuan_baku_mutu_detail_parameter_klinik = $req['parameter_sub_satuan_baku_mutu_detail_parameter_klinik'][$i];
            $post_sub_parameter->unit_id_baku_mutu_detail_parameter_klinik = $req['unit_id_baku_mutu_detail_parameter_klinik'][$i];
            $post_sub_parameter->min_baku_mutu_detail_parameter_klinik = $req['min_baku_mutu_detail_parameter_klinik'][$i];
            $post_sub_parameter->max_baku_mutu_detail_parameter_klinik = $req['max_baku_mutu_detail_parameter_klinik'][$i];
            $post_sub_parameter->equal_baku_mutu_detail_parameter_klinik = isset($req['equal_baku_mutu_detail_parameter_klinik'][$i]) && $req['equal_baku_mutu_detail_parameter_klinik'][$i] ? rubahNilaikeHtml(str_replace(",", ".", $req['equal_baku_mutu_detail_parameter_klinik'][$i])) : null;
            $post_sub_parameter->nilai_baku_mutu_detail_parameter_klinik = isset($req['nilai_baku_mutu_detail_parameter_klinik'][$i]) && $req['nilai_baku_mutu_detail_parameter_klinik'][$i] ? rubahNilaikeHtml(str_replace(",", ".", $req['nilai_baku_mutu_detail_parameter_klinik'][$i])) : null;

            $simpan_post_sub_parameter = $post_sub_parameter->save();
          }
        }
      }



    
      
      if ($simpan == true) {
        // Jangan commit di sini karena transaction di-handle di method store()
        // DB::commit() akan dipanggil di method store() setelah semua operasi selesai
        
        // Return model atau true untuk indikasi sukses, bukan JsonResponse
        return $mainModel ?: true;
      
      } else {
        // Throw exception agar bisa di-catch di method store()
        throw new \Exception("Data baku mutu klinik tidak berhasil disimpan!");
      }
    } catch (\Exception $e) {
      // Jangan rollback di sini karena transaction di-handle di method store()
      // DB::rollback() akan dipanggil di method store() jika ada error
      
      // Throw exception agar bisa di-catch di method store()
      throw $e;
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $item = BakuMutu::find($id);

    $get_lab = Laboratorium::find($item->lab_id);
    $lab_link = strtolower($get_lab->nama_laboratorium);
    $lab = $get_lab->nama_laboratorium;

    // cek apakah di baku mutu memiliki sub parameter satuan
    $parameter_sub_satuan_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $id)
      ->whereHas('parametersubsatuanklinik', function ($query) {
        $query->orderBy('created_at', 'asc')
          ->whereNull('deleted_at');
      })
      ->get();

    if (count($parameter_sub_satuan_baku_mutu) > 0) {
      $data_parameter_sub_satuan_baku_mutu = $parameter_sub_satuan_baku_mutu;
    } else {
      $data_parameter_sub_satuan_baku_mutu = [];
    }

    return view('masterweb::module.admin.laboratorium.baku-mutu-klinik.show', [
      'item' => $item,
      'lab_link' => $lab_link,
      'lab' => $lab,
      'data_parameter_sub_satuan_baku_mutu' => $data_parameter_sub_satuan_baku_mutu
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $item = BakuMutu::with('parametersatuanklinik')->find($id);

    $get_lab = Laboratorium::find($item->lab_id);
    $lab_link = strtolower($get_lab->nama_laboratorium);
    $lab = $get_lab->nama_laboratorium;

    // cek apakah di baku mutu memiliki sub parameter satuan
    $parameter_sub_satuan_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $id)
      ->whereHas('parametersubsatuanklinik', function ($query) {
        $query->orderBy('created_at', 'asc')
          ->whereNull('deleted_at');
      })
      ->get();

    if (count($parameter_sub_satuan_baku_mutu) > 0) {
      $data_parameter_sub_satuan_baku_mutu = $parameter_sub_satuan_baku_mutu;
    } else {
      $data_parameter_sub_satuan_baku_mutu = [];
    }

    // Get number format dari parameter satuan (default 'en' jika tidak ada)
    $numberFormat = $item->parametersatuanklinik->number_format ?? 'en';

    return view('masterweb::module.admin.laboratorium.baku-mutu-klinik.edit', [
      'item' => $item,
      'lab_link' => $lab_link,
      'lab' => $lab,
      'data_parameter_sub_satuan_baku_mutu' => $data_parameter_sub_satuan_baku_mutu,
      'numberFormat' => $numberFormat
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $validator = $this->rules($request->all());

    if ($validator->fails()) {
      return response()->json(['status' => false, 'pesan' => $validator->errors()]);
    } else {
      // check parameter jenis dan satuan
      $post = BakuMutu::find($id);

      // Cek duplikat: cari record lain (bukan current $id) yang memiliki kombinasi identik
      // (parameter_jenis, satuan, is_khusus, is_haji, gender, umur)
      $isHaji = (int) ($request->is_haji ?? 0);

      $duplicateQuery = BakuMutu::where('parameter_jenis_klinik_id', $request->parameter_jenis_klinik_id)
              ->where('parameter_satuan_klinik_id', $request->parameter_satuan_klinik_id)
        ->where('is_khusus_baku_mutu', $request->is_khusus_baku_mutu)
        ->where('is_haji', $isHaji)
        ->where('id_baku_mutu', '!=', $id)
        ->whereNull('deleted_at');

      // Untuk baku mutu haji: umur selalu NULL, cukup match gender
      if ($isHaji) {
        if ($request->filled('gender_baku_mutu')) {
          $duplicateQuery->where('gender_baku_mutu', $request->gender_baku_mutu);
            } else {
          $duplicateQuery->whereNull('gender_baku_mutu');
        }
        $duplicateQuery->whereNull('minimal_umur_baku_mutu')->whereNull('maksimal_umur_baku_mutu');
      } elseif ($request->is_khusus_baku_mutu == "1") {
        // Data khusus non-haji: match tepat gender & umur
        if ($request->filled('gender_baku_mutu')) {
          $duplicateQuery->where('gender_baku_mutu', $request->gender_baku_mutu);
        } else {
          $duplicateQuery->whereNull('gender_baku_mutu');
        }
        if ($request->filled('minimal_umur_baku_mutu')) {
          $duplicateQuery->where('minimal_umur_baku_mutu', $request->minimal_umur_baku_mutu);
      } else {
          $duplicateQuery->whereNull('minimal_umur_baku_mutu');
        }
        if ($request->filled('maksimal_umur_baku_mutu')) {
          $duplicateQuery->where('maksimal_umur_baku_mutu', $request->maksimal_umur_baku_mutu);
          } else {
          $duplicateQuery->whereNull('maksimal_umur_baku_mutu');
          }
      }

      $duplicate = $duplicateQuery->first();

      if ($duplicate) {
        return response()->json(['status' => false, 'pesan' => "Parameter sudah pernah dibuat di baku mutu dengan data yang sama, silahkan pilih parameter jenis, satuan, atau gender/umur yang berbeda!"], 200);
          } else {
          return $this->storeUpdateBakuMutu($request->all(), $id);
      }
    }
  }

  private function nilaiBakuMutuForPermohonanStorage($nilai, $inputNumberFormat = null)
  {
    if ($nilai === null || $nilai === '') {
      return null;
    }
    if (is_string($nilai) && strpos($nilai, '[[BMHTML]]') === 0) {
      return $nilai;
    }
    return rubahNilaikeHtml($nilai, $inputNumberFormat);
  }

  private function updateBakuMutuPermohonanUji($request, $id_parameter){
    $all_permohonanUjiParameterKlinik = PermohonanUjiParameterKlinik::where('parameter_satuan_klinik',$id_parameter)->whereYear('created_at', date('Y'))->get();

    // Get number format dari parameter satuan
    $parameterSatuan = \Smt\Masterweb\Models\ParameterSatuanKlinik::find($id_parameter);
    $inputNumberFormat = $parameterSatuan ? ($parameterSatuan->number_format ?? 'en') : 'en';
    $nilaiStored = $this->nilaiBakuMutuForPermohonanStorage($request['nilai_baku_mutu'] ?? null, $inputNumberFormat);

    foreach ($all_permohonanUjiParameterKlinik as $permohonanUjiParameterKlinik) {
      // $data_permohonan_uji_parameter_satuan = PermohonanUjiParameterSatuan::where('permohonan_uji_parameter_klinik_id', $permohonanUjiParameterKlinik->id_permohonan_uji_parameter_klinik
      if ($request['is_khusus_baku_mutu'] == "1") {
        $request['gender_baku_mutu'];

        if ($permohonanUjiParameterKlinik->permohonanujiklinik->pasien->gender_pasien == $request['gender_baku_mutu']) {
          $permohonanUjiParameterKlinik->baku_mutu_permohonan_uji_parameter_klinik = $nilaiStored;
        }

       
      }else{
        $permohonanUjiParameterKlinik->baku_mutu_permohonan_uji_parameter_klinik = $nilaiStored;
      }  
      $permohonanUjiParameterKlinik->save();
    }
  }

  private function storeUpdateBakuMutu($request, $id)
  {
    DB::beginTransaction();

    try {
    $post = BakuMutu::find($id);
    $post->library_id = $request['library_id'];
    $post->lab_id = $request['lab_id'];
    $post->parameter_jenis_klinik_id = $request['parameter_jenis_klinik_id'];
    $post->parameter_satuan_klinik_id = $request['parameter_satuan_klinik_id'];
    $post->unit_id = $request['unit_id'];
    $post->is_sub_parameter_satuan_baku_mutu = $request['is_sub_parameter_satuan_baku_mutu'];
    $post->is_khusus_baku_mutu = $request['is_khusus_baku_mutu'];
    $post->is_haji = (int)($request['is_haji'] ?? 0);
    $id_parameter = $request['parameter_satuan_klinik_id'];

    // Get number format dari parameter satuan untuk konversi input
    $parameterSatuan = \Smt\Masterweb\Models\ParameterSatuanKlinik::find($id_parameter);
    $inputNumberFormat = $parameterSatuan ? ($parameterSatuan->number_format ?? 'en') : 'en';

    if ($request['is_khusus_baku_mutu'] == "1") {
      $post->minimal_umur_baku_mutu = $request['minimal_umur_baku_mutu'];
      $post->maksimal_umur_baku_mutu = $request['maksimal_umur_baku_mutu'];
      $post->gender_baku_mutu = $request['gender_baku_mutu'];
      $post->kesimpulan_baku_mutu = $request['kesimpulan_baku_mutu'] ?? null;
      $post->is_normal = isset($request['is_normal']) && $request['is_normal'] ? 1 : 0;
    } else {
      $post->minimal_umur_baku_mutu = null;
      $post->maksimal_umur_baku_mutu = null;
      $post->gender_baku_mutu = null;
      $post->kesimpulan_baku_mutu = null;
      $post->is_normal = 0;
    }

    // jika dibaku mutu untuk parameter memiliki sub satuan
    if ($request['is_sub_parameter_satuan_baku_mutu'] == 1) {
      if ($request['parameter_sub_satuan_baku_mutu_detail_parameter_klinik'] != null) {
        $count_sub_parameter_satuan = count($request['parameter_sub_satuan_baku_mutu_detail_parameter_klinik']);

        $check_delete = DB::table('tb_baku_mutu_detail_parameter_klinik')->where('baku_mutu_id', $id)->get();

        if (count($check_delete) > 0) {
          DB::table('tb_baku_mutu_detail_parameter_klinik')->where('baku_mutu_id', $id)->delete();
        }

        for ($i = 0; $i < $count_sub_parameter_satuan; $i++) {
          $post_sub_parameter = new BakuMutuDetailParameterKlinik();
          $post_sub_parameter->baku_mutu_id = $id;
          $post_sub_parameter->parameter_sub_satuan_baku_mutu_detail_parameter_klinik = $request['parameter_sub_satuan_baku_mutu_detail_parameter_klinik'][$i];
          $post_sub_parameter->unit_id_baku_mutu_detail_parameter_klinik = $request['unit_id_baku_mutu_detail_parameter_klinik'][$i];
          $post_sub_parameter->min_baku_mutu_detail_parameter_klinik = $request['min_baku_mutu_detail_parameter_klinik'][$i];
          $post_sub_parameter->max_baku_mutu_detail_parameter_klinik = $request['max_baku_mutu_detail_parameter_klinik'][$i];
          $post_sub_parameter->equal_baku_mutu_detail_parameter_klinik = isset($request['equal_baku_mutu_detail_parameter_klinik'][$i]) && $request['equal_baku_mutu_detail_parameter_klinik'][$i] ? rubahNilaikeHtml($request['equal_baku_mutu_detail_parameter_klinik'][$i], $inputNumberFormat) : null;
          $post_sub_parameter->nilai_baku_mutu_detail_parameter_klinik = isset($request['nilai_baku_mutu_detail_parameter_klinik'][$i]) && $request['nilai_baku_mutu_detail_parameter_klinik'][$i] ? rubahNilaikeHtml($request['nilai_baku_mutu_detail_parameter_klinik'][$i], $inputNumberFormat) : null;

          $simpan_post_sub_parameter = $post_sub_parameter->save();
        }

        $post->min = null;
        $post->max = null;
        $post->equal = null;
        $post->nilai_baku_mutu = null;
      }
    }

    if ($request['is_sub_parameter_satuan_baku_mutu'] == 0) {
      $post->min = $request['min'];
      $post->max = $request['max'];
      $post->equal = (isset($request['equal']) && $request['equal'] && trim($request['equal']) !== '') ? rubahNilaikeHtml($request['equal'], $inputNumberFormat) : null;
      $post->nilai_baku_mutu = isset($request['nilai_baku_mutu']) && $request['nilai_baku_mutu'] ? rubahNilaikeHtml($request['nilai_baku_mutu'], $inputNumberFormat) : null;

      $check_delete = DB::table('tb_baku_mutu_detail_parameter_klinik')->where('baku_mutu_id', $id)->get();

      if (count($check_delete) > 0) {
        DB::table('tb_baku_mutu_detail_parameter_klinik')->where('baku_mutu_id', $id)->delete();
      }
    }

    // Handle is_option dan option
    $post->is_option = isset($request['is_option']) && $request['is_option'] ? 1 : 0;
    if ($post->is_option == 1) {
      $post->option = $request['option'] ?? null;
    } else {
      $post->option = null;
    }

    $simpan = $post->save();

    $syncedPermohonan = 0;
    $syncPermohonan = (int) ($request['sync_permohonan'] ?? 0) === 1;
    $syncFromDate = trim((string) ($request['sync_permohonan_from_date'] ?? ''));
    if ($syncPermohonan && (int) ($request['is_sub_parameter_satuan_baku_mutu'] ?? 0) === 0) {
      if ($syncFromDate === '') {
        throw new \RuntimeException('Tanggal mulai penyesuaian permohonan wajib diisi');
      }
      $ctx = [
        'satuan' => (string) $id_parameter,
        'jenis' => (string) ($request['parameter_jenis_klinik_id'] ?? ''),
        'is_haji' => (int) ($request['is_haji'] ?? 0),
        'unit_id' => $request['unit_id'] ?? $post->unit_id ?? null,
      ];
      $itemsQuery = BakuMutu::query();
      $this->applyReplaceGroupScope($itemsQuery, $ctx);
      $items = $itemsQuery->get();
      $syncedPermohonan = $this->syncPermohonanBakuMutuFromDate($ctx, $items, $syncFromDate);
    }

    DB::commit();

    if ($simpan == true) {
      return response()->json([
        'status' => true,
        'pesan' => 'Data baku mutu klinik berhasil diubah!',
        'synced_permohonan' => $syncedPermohonan,
      ], 200);
    }

    return response()->json(['status' => false, 'pesan' => 'Data baku mutu klinik tidak berhasil diubah!'], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('storeUpdateBakuMutu failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
      return response()->json(['status' => false, 'pesan' => $e->getMessage()], 200);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $hapus = BakuMutu::find($id)->delete();
    BakuMutuDetailParameterKlinik::where('baku_mutu_id',$id)->delete();

    if ($hapus == true) {
      return response()->json(['status' => true, 'pesan' => "Data parameter satuan berhasil dihapus!"], 200);
    } else {
      return response()->json(['status' => false, 'pesan' => "Data parameter satuan tidak berhasil dihapus!"], 200);
    }
  }

  public function checkBakuMutuParameterKlinik(Request $request)
  {
    $response = array();
    $parameter_jenis = $request->parameter_jenis;
    $parameter_satuan = $request->parameter_satuan;

    $check = BakuMutu::where('parameter_jenis_klinik_id', $parameter_jenis)
      ->where('parameter_satuan_klinik_id', $parameter_satuan)
      ->first();

    if ($check !== null) {
      return response()->json(['status' => true, 'pesan' => "Maaf, Anda tidak bisa menambahkan lebih parameter yang sama di Baku Mutu Klinik!"], 200);
    }
  }

  public function getBakuMutuKlinik(Request $request)
  {
    $search = $request->search;

    if ($search == '') {
      $data = BakuMutu::orderby('name_report', 'asc')
        ->select('id_baku_mutu', 'name_report')
        ->limit(10)
        ->get();
    } else {
      $data = BakuMutu::orderby('name_report', 'asc')
        ->select('id_baku_mutu', 'name_report')
        ->where('name_report', 'like', '%' . $search . '%')
        ->limit(10)
        ->get();
    }

    $response = array();
    foreach ($data as $item) {
      $response[] = array(
        "id" => $item->id_baku_mutu,
        "text" => $item->name_report
      );
    }

    return response()->json($response);
  }

  public function checkBakuMutuSubParameterSatuan(Request $request)
  {
    $get_parameter_jenis = $request->parameter_jenis;
    $get_parameter_satuan = $request->parameter_satuan;

    /* $check_parameter = ParameterSatuanKlinik::where('id_parameter_satuan_klinik', $get_parameter_satuan)
            ->where('parameter_jenis_klinik', $get_parameter_jenis)
            ->get(); */

    $check_sub_parameter = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $get_parameter_satuan)
      ->orderBy('name_parameter_sub_satuan_klinik', 'asc')
      ->get();

    if (count($check_sub_parameter) > 0) {
      return response()->json(['status' => true, 'data_sub' => $check_sub_parameter], 200);
    } else {
      return response()->json(['status' => false, 'data_sub' => []], 200);
    }
  }
}