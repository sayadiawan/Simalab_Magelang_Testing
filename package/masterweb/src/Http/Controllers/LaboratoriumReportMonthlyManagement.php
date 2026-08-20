<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;

use PDF;
use DB;
use Mapper;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;


class LaboratoriumReportMonthlyManagement extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('masterweb::module.admin.laboratorium.report.report-monthly.list');
    }

    public function data_report_monthly(Request $request)
    {
        $datas = PermohonanUjiKlinik::orderBy('created_at')->get();

        return Datatables::of($datas)
            ->addColumn('action', function ($data) {
                $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
                    ->whereNull('deleted_at')
                    ->get();

                $analisButton = '';

                if (count($data_permohonan_uji_paket_klinik) > 0) {
                    $analisButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.create-permohonan-uji-analis', $data->id_permohonan_uji_klinik) . '" title="Analisa">Analis</a> ';
                }

                $parameterButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.create-permohonan-uji-parameter', $data->id_permohonan_uji_klinik) . '" title="Masukkan Parameter">Parameter</a> ';

                $detailButton = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.show', $data->id_permohonan_uji_klinik) . '" title="Detail">Detail</a> ';

                $editButton = '<a href="' . route('elits-permohonan-uji-klinik.edit', $data->id_permohonan_uji_klinik) . '" class="dropdown-item" title="Edit">Edit</a> ';

                $deleteButton = '<a class="dropdown-item btn-hapus" href="#hapus" data-id="' . $data->id_permohonan_uji_klinik  . '" data-nama="' . $data->namapasien_permohonan_uji_klinik . '" title="Hapus">Hapus</a> ';

                $button = '<div class="dropdown show m-1">
                            <a class="btn btn-fw btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Aksi
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $analisButton . '

                                ' . $parameterButton . '

                                ' . $detailButton . '

                                ' . $editButton . '

                                ' . $deleteButton . '
                            </div>
                        </div>';

                // BUTTON PRINT
                $printHasilKlinik = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil', $data->id_permohonan_uji_klinik) . '" target="__blank" title="Print Hasil Klinik">Print Hasil Klinik</a> ';

                $data_permohonan_uji_parameter_klinik_rapid_antibody = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
                    ->whereHas('parametersatuanklinik', function ($query) {
                        return $query->orderBy('name_parameter_satuan_klinik', 'asc')
                            ->where('jenis_pemeriksaan_parameter_satuan_klinik', '2')
                            ->whereNull('deleted_at');
                    })
                    ->get();

                if (count($data_permohonan_uji_parameter_klinik_rapid_antibody) > 0) {
                    $printHasilRapidAntibody = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-rapid-antibody', $data->id_permohonan_uji_klinik) . '" title="Print Hasil Rapid Antibody" target="__blank">Print Hasil Rapid Antibody</a> ';
                } else {
                    $printHasilRapidAntibody = '';
                }

                $data_permohonan_uji_parameter_klinik_rapid_antigen = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
                    ->whereHas('parametersatuanklinik', function ($query) {
                        return $query->orderBy('name_parameter_satuan_klinik', 'asc')
                            ->where('jenis_pemeriksaan_parameter_satuan_klinik', '3')
                            ->whereNull('deleted_at');
                    })
                    ->get();

                if (count($data_permohonan_uji_parameter_klinik_rapid_antigen) > 0) {
                    $printHasilRapidAntigen = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-rapid-antigen', $data->id_permohonan_uji_klinik) . '" title="Print Hasil Rapid Antigen" target="__blank">Print Hasil Rapid Antigen</a> ';
                } else {
                    $printHasilRapidAntigen = '';
                }

                $data_permohonan_uji_parameter_klinik_pcr = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)
                    ->whereHas('parametersatuanklinik', function ($query) {
                        return $query->orderBy('name_parameter_satuan_klinik', 'asc')
                            ->where('jenis_pemeriksaan_parameter_satuan_klinik', '1')
                            ->whereNull('deleted_at');
                    })
                    ->get();

                if (count($data_permohonan_uji_parameter_klinik_pcr) > 0) {
                    $printHasilPcr = '<a class="dropdown-item" href="' . route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-hasil-pcr', $data->id_permohonan_uji_klinik) . '" title="Print Hasil PCR" target="__blank">Print Hasil PCR</a> ';
                } else {
                    $printHasilPcr = '';
                }

                $buttonPrint = '<div class="dropdown show m-1">
                            <a class="btn btn-fw btn-light dropdown-toggle" href="#" role="button" id="dropdownMenuLinkPrint" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Print
                            </a>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                ' . $printHasilKlinik . '

                                ' . $printHasilRapidAntibody . '

                                ' . $printHasilRapidAntigen . '

                                ' . $printHasilPcr . '
                            </div>
                        </div>';

                return $button . ' ' . $buttonPrint;
            })
            ->addColumn('tgl_pengambilan', function ($data) {
                // return Carbon::createFromFormat('Y-m-d H:i:S', $data->tglpengambilan_permohonan_uji_klinik, 'Asia/Jakarta')->isoFormat('D MMMM Y H:i');

                return Carbon::createFromFormat('Y-m-d H:i:s', $data->tglpengambilan_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
            })
            ->addColumn('status_permohonan', function ($data) {
                $status_parameter = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $data->id_permohonan_uji_klinik)->whereNull('deleted_at')->get();

                if (count($status_parameter) > 0) {
                    $status = '<label class="badge badge-primary badge-pill" title="Proses Analisa">Proses Analisa</label>';
                } else {
                    $status = '<label class="badge badge-warning badge-pill" title="Proses Parameter">Proses Parameter</label>';
                }

                if ($data->tglpengujian_permohonan_uji_klinik || $data->spesimen_darah_permohonan_uji_klinik || $data->spesimen_urine_permohonan_uji_klinik) {
                    $status = '<label class="badge badge-success badge-pill" title="Proses Selesai">Proses Selesai</label>';
                }

                return $status;
            })
            ->rawColumns(['action', 'tgl_pengambilan', 'status_permohonan'])
            ->addIndexColumn() //increment
            ->make(true);
    }
}
