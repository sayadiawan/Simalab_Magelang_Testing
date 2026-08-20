@extends('masterweb::template.admin.layout_scan')
@section('title')
    Proses Pengujian
@endsection

@section('content')

   

    <style>
   
    </style>

   


   


    <div class="card" style="margin-bottom: 20px">
        <div class="card-header">
            
            <H4>Proses Pengujian</H4>
        </div>
        <div class="card-body">
            <div class="col-md-12">
            
                <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="row">
                            <div class="col-md-3">
                                <label for="codesample_samples">Nama Perusahaan</label>
                            </div>
                            <div class="col-md-0">
                                <label for="codesample_samples">:</label>
                            </div>
                            <div class="col-md-8">
                                <label for="codesample_samples">{{$permohonan_uji->name_customer}}</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="codesample_samples">Tanggal</label>
                            </div>
                            <div class="col-md-0">
                                <label for="codesample_samples">:</label>
                            </div>
                            <div class="col-md-8">
                                @php
                                $date_permohonan_uji=\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $permohonan_uji->date_permohonan_uji, 'Asia/Jakarta')->isoFormat('D MMMM Y');
                                @endphp
                                <label for="codesample_samples">{{$date_permohonan_uji}}</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="codesample_samples">Catatan</label>
                            </div>
                            <div class="col-md-0">
                                <label for="codesample_samples">:</label>
                            </div>
                            <div class="col-md-8">
                                <label for="codesample_samples">{{$permohonan_uji->catatan}}</label>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
    </div>


    @foreach ($samples as $sample)

        @php
             $samplelabs= \Smt\Masterweb\Models\Sample::where('id_samples','=',$sample->id_samples)
                            ->join('tb_sample_method', function ($join) {
                                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                                ->whereNull('tb_sample_method.deleted_at')
                                ->whereNull('tb_samples.deleted_at')
                                ->join('ms_laboratorium', function ($join) {
                                    $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                                    ->whereNull('ms_laboratorium.deleted_at')
                                    ->whereNull('tb_sample_method.deleted_at');
                                });
                            })
                            ->select('ms_laboratorium.id_laboratorium','ms_laboratorium.nama_laboratorium','tb_pengesahan_hasil.*','tb_verifikasi_hasil.*','tb_pengetikan_hasil.*','tb_pelaporan_hasil.*','tb_samples.*','ms_sample_type.*','tb_sample_penerimaan.*','tb_sample_penanganan.*')
                            ->join('ms_sample_type', function ($join) {
                                $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                                ->whereNull('ms_sample_type.deleted_at')
                                ->whereNull('tb_samples.deleted_at');
                            })
                            ->leftjoin('tb_sample_penerimaan', function ($join) {
                                $join->on('tb_samples.id_samples', '=', 'tb_sample_penerimaan.sample_id')
                                ->whereNull('tb_sample_penerimaan.deleted_at')
                                ->whereNull('tb_samples.deleted_at');
                            })
                            ->leftjoin('tb_sample_penanganan', function ($join) {
                                $join->on('tb_sample_penanganan.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                                ->on('tb_samples.id_samples', '=', 'tb_sample_penanganan.sample_id')
                                ->whereNull('tb_sample_penanganan.deleted_at')
                                ->whereNull('tb_samples.deleted_at');
                            })
                            ->leftjoin('tb_pelaporan_hasil', function ($join) {
                                $join->on('tb_pelaporan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                                ->on('tb_samples.id_samples', '=', 'tb_pelaporan_hasil.sample_id')
                                ->whereNull('tb_pelaporan_hasil.deleted_at')
                                ->whereNull('tb_samples.deleted_at');
                            })
                            ->leftjoin('tb_pengetikan_hasil', function ($join) {
                                $join->on('tb_pengetikan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                                ->on('tb_samples.id_samples', '=', 'tb_pengetikan_hasil.sample_id')
                                ->whereNull('tb_pengetikan_hasil.deleted_at')
                                ->whereNull('tb_samples.deleted_at');
                            })
                            ->leftjoin('tb_verifikasi_hasil', function ($join) {
                                $join->on('tb_verifikasi_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                                ->on('tb_samples.id_samples', '=', 'tb_verifikasi_hasil.sample_id')
                                ->whereNull('tb_verifikasi_hasil.deleted_at')
                                ->whereNull('tb_samples.deleted_at');
                            })
                            ->leftjoin('tb_pengesahan_hasil', function ($join) {
                                    $join->on('tb_pengesahan_hasil.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                                    ->on('tb_samples.id_samples', '=', 'tb_pengesahan_hasil.sample_id')
                                    ->whereNull('tb_pengesahan_hasil.deleted_at')
                                    ->whereNull('tb_samples.deleted_at');
                                })
                            ->distinct('id_laboratorium')
                            
                            ->get();
                            
                // dd($samplelabs);
        @endphp

        <div class="card"  style="margin-bottom: 20px">
            <div class="card-header" style="background-color: #17a2b8">
                
                <H6 style="color: white"><b>Kode Sample : {!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</b></H6>
            </div>
            <div class="card-body">
                <div class="col-md-12">
                  
                @foreach ($samplelabs as $samplelab)
                <div class="col-md-12" style="border:1px solid black;  padding: 20px;">
                    <h6 class="d-inline-block card-subtitle text-muted float-left margin-top" style="margin-top:.2rem; background:blue;color:white !important;padding:5px;border-radius:5px;">Laboratorium {{$samplelab->nama_laboratorium}}</h6>
                    {{-- <a href="{{route('scan.print', [$sample->id_samples,$samplelab->id_laboratorium])}}">
                        <h6 class="d-inline-block card-subtitle text-muted float-right margin-top" style="margin-top:.2rem; background:blue;color:white !important;padding:5px;border-radius:5px;">Laboratorium {{$samplelab->nama_laboratorium}}</h6>
                    </a> --}}
                    <table class="table table-bordered">
                        <tr>
                            <th><b>Laboratorium</b></th>
                            <td>{{$samplelab->nama_laboratorium}}</td>
                            <th><b>Tanggal Pengambilan</b></th>
                            <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->datesampling_samples)->isoFormat('D MMMM Y')}}</td>
                            <th><b>Jenis Sampel</b></th>
                            <td>{{$samplelab->name_sample_type}}</td>
                        </tr>
                        <tr>
                            <th><b>Nomor Sampel</b></th>
                            <td>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($samplelab) !!}</td>
                            <th><b>Tanggal Pengiriman</b></th>
                            <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->date_sending, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}</td>
                            <th><b>Nama Pengambil</b></th>
                            <td>{{$samplelab->nama_pengambil}}</td>
                        </tr>
                    </table> 
                    <br>
                    @php
                        $laboratoriummethods = \Smt\Masterweb\Models\SampleMethod::where('laboratorium_id','=',$samplelab->id_laboratorium)
                                        ->where('sample_id','=',$samplelab->id_samples)
                                    ->orderBy('ms_method.created_at')
                                    ->join('ms_method', function ($join) {
                                        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                                        ->whereNull('tb_sample_method.deleted_at')
                                        ->whereNull('ms_method.deleted_at');
                                    })
                                    ->get();
                        $laboratoriumprogress =\Smt\Masterweb\Models\LaboratoriumProgress::where('laboratorium_id',$samplelab->id_laboratorium)->orderBy('order_sort','asc')->get();
                        $laboratoriumsampleanalitikprogress =\Smt\Masterweb\Models\SampleAnalitikProgress::where('tb_sample_analitik_progress.laboratorium_id',$samplelab->id_laboratorium)
                                                                ->where('tb_sample_analitik_progress.sample_id',$samplelab->id_samples)
                                                                ->orderBy('tb_laboratorium_progress.order_sort','asc')
                                                                ->join('tb_laboratorium_progress', function ($join) {
                                                                    $join->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
                                                                    ->on('tb_laboratorium_progress.laboratorium_id', '=', 'tb_sample_analitik_progress.laboratorium_id')
                                                                    ->whereNull('tb_laboratorium_progress.deleted_at')
                                                                    ->whereNull('tb_sample_analitik_progress.deleted_at');
                                                                })
                                                                ->get();
                        $penerimaan_sample =\Smt\Masterweb\Models\PenerimaanSample::where('laboratorium_id',$samplelab->id_laboratorium)->first();
                    @endphp
                     <h5>Parameter {{$samplelab->nama_laboratorium}} :</h5>
                     @foreach ($laboratoriummethods as $laboratoriummethod)
                         - {{$laboratoriummethod->params_method}}<br>
                     @endforeach
                     <br>
                     <div class="col-md-12">
                         <div class="row">
                             <div class="col-md-4">
                                 <table class="table table-bordered">
                                     <thead>
                                         <tr>
                                             <th>Pra Analitik</th>
                                             <th>Tanggal</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                                         <tr>
                                             <td>Permintaan Pemeriksaan</td>
                                             <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->date_sending, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}</td>
                                             
                                         </tr>
                                         <tr>
                                             <td>Persiapan Sampel</td>
                                             <td>
                                                 {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->date_sending, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                             </td>
                                             
                                         </tr>
                                         <tr>
                                             <td>Pengambilan Sampel</td>
                                             <td>
                                                 {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->datesampling_samples, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                             </td>
                                             
                                         </tr>
                                         <tr>
                                             <td>Penerimaan Sampel</td>
                                             <td>
                                                



                                                 @if (isset($sample->pengesahan_hasil_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penerimaan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                 @elseif(isset($samplelab->verifikasi_hasil_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penerimaan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                
                                                 @elseif(isset($samplelab->pelaporan_hasil_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penerimaan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                 @elseif(isset($samplelab->penanganan_sample_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penerimaan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                 @elseif(isset($samplelab->penerimaan_sample_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penerimaan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                 @else
                                                    <button type="button" class="btn btn-warning btn-rounded ">
                                                        <i class="fa fa-spinner"></i> Sedang Proses                                                 
                                                    </button> 
                                                 @endif
                                                
                                             </td>
                                         </tr>
                                         <tr>
                                             <td>Penanganan Sampel</td>
                                             <td>
                                                 @if (isset($samplelab->pengesahan_hasil_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penanganan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                 @elseif(isset($samplelab->verifikasi_hasil_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penanganan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                              
                                                 @elseif(isset($samplelab->pelaporan_hasil_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penanganan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                 @elseif(isset($samplelab->penanganan_sample_date))
                                                     {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->penanganan_sample_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                 @elseif(isset($samplelab->penerimaan_sample_date))
                                                        <button type="button" class="btn btn-warning btn-rounded ">
                                                            <i class="fa fa-spinner"></i> Sedang Proses                                                 
                                                         </button> 
                                                 @else
                                                

                                                     <button type="button" class="btn btn-secondary btn-rounded" disabled><i class="fa fa-spinner"></i> Belum Proses</button>
                                                   
                                                 @endif
                                             </td>
                                         </tr>

                                     </tbody>
                                    
                                 </table>
                             </div>
                             <div class="col-md-4">
                                 <table class="table table-bordered">
                                     <thead>
                                         <tr>
                                             <th>Analitik</th>
                                             <th>Tanggal</th>
                                         </tr>
                                         @php
                                             $i=0;
                                         @endphp
                                         @foreach ($laboratoriumprogress as $laboratoriumprogres)
                                         <tr>
                                             <td>{{$laboratoriumprogres->name_progress}}</td>
                                             <td>
                                                 @if(isset($samplelab->penanganan_sample_date))
                                                     
                                                     @if(isset($laboratoriumsampleanalitikprogress[$i-1]->date_done))
                                                         @if(isset($laboratoriumsampleanalitikprogress[$i]->date_done))
                                                             {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $laboratoriumsampleanalitikprogress[$i]->date_done, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                         @else
                                                             @php
                                                                 $routes='elits-'.$laboratoriumprogres->link.'.index';
                                                             @endphp
                                                            
                                                            <button type="button" class="btn btn-warning btn-rounded ">
                                                                <i class="fa fa-spinner"></i> Sedang Proses                                                  
                                                            </button> 
                                                         @endif
                                                         
                                                     @else
                                                         @if($i==0)
                                                             @if(isset($laboratoriumsampleanalitikprogress[$i]->date_done))
                                                                 {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $laboratoriumsampleanalitikprogress[$i]->date_done, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                             @else
                                                                 @php
                                                                     $routes='elits-'.$laboratoriumprogres->link.'.index';
                                                                 @endphp
                                                                    <button type="button" class="btn btn-warning btn-rounded ">
                                                                        <i class="fa fa-spinner"></i> Sedang Proses                                                  
                                                                     </button> 
                                                             @endif
                                                         @else
                                                             <button type="button" class="btn btn-secondary btn-rounded" disabled> <i class="fa fa-spinner"></i> Belum Proses </button>
                                                   
                                                         @endif
                                                     @endif
                                                 @else
                                                 <button type="button" class="btn btn-secondary btn-rounded" disabled> <i class="fa fa-spinner"></i> Belum Proses </button>
                                                   
                                                 @endif
                                             </td>
                                         </tr>
                                         @php
                                             $i++;
                                             
                                         @endphp  
                                         @endforeach
                                       
                                         
                                     </thead>
                                    
                                 </table>
                             </div>
                             <div class="col-md-4">
                                 <table class="table table-bordered">
                                     <thead>
                                         <tr>
                                             <th>Paska Analitik</th>
                                             <th>Tanggal</th>
                                         </tr>
                                         <tr>
                                             <td>Pelaporan Hasil</td>
                                             <td>
                                                 @if (isset($laboratoriumsampleanalitikprogress[count($laboratoriumsampleanalitikprogress)-1]->link) && ($laboratoriumsampleanalitikprogress[count($laboratoriumsampleanalitikprogress)-1]->link=="baca-hasil"))
                                                     @if (isset($samplelab->pengesahan_hasil_date))
                                                         {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->pelaporan_hasil_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                     @elseif(isset($samplelab->verifikasi_hasil_date))
                                                         {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->pelaporan_hasil_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                   
                                                     @elseif(isset($samplelab->pelaporan_hasil_date))
                                                         {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->pelaporan_hasil_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                     @elseif(isset($samplelab->penanganan_sample_date))
                                                        <button type="button" class="btn btn-warning btn-rounded ">
                                                            <i class="fa fa-spinner"></i> Sedang Proses                                                 
                                                        </button> 
                                                     @else
                                                     <button type="button" class="btn btn-secondary btn-rounded" disabled><i class="fa fa-spinner"></i> Belum Proses </button>
                                                   
                                                     @endif
                                                 @else
                                                 <button type="button" class="btn btn-secondary btn-rounded" disabled><i class="fa fa-spinner"></i> Belum Proses </button>
                                                   
                                                 @endif
                                             </td>
                                             
                                         </tr>
                                        
                                         <tr>
                                             <td>Verifikasi Hasil</td>
                                             <td>
                                                 @if (isset($samplelab->date_analitik_sample))
                                                     @if (isset($samplelab->pengesahan_hasil_date))
                                                         {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->verifikasi_hasil_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                     @elseif(isset($samplelab->verifikasi_hasil_date))
                                                         {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samplelab->verifikasi_hasil_date, 'Asia/Jakarta')->isoFormat('D MMMM Y')}}
                                                     @elseif(isset($samplelab->pelaporan_hasil_date))
                                                            <button type="button" class="btn btn-warning btn-rounded ">
                                                                <i class="fa fa-spinner"></i> Sedang Proses                                                 
                                                             </button> 
                                                         
                                                     @else
                                                     <button type="button" class="btn btn-secondary btn-rounded" disabled><i class="fa fa-spinner"></i> Belum Proses</button>
                                                   
                                                     @endif
                                                 @else
                                                 <button type="button" class="btn btn-secondary btn-rounded" disabled><i class="fa fa-spinner"></i> Belum Proses</button>
                                                   
                                                 @endif
                                             </td>
                                         </tr>
                                         <tr>
                                             <td>Pengesahan Hasil</td>
                                             <td>
                                                 @if (isset($samplelab->date_analitik_sample))
                                                     @if (isset($samplelab->pengesahan_hasil_date))
                                                     <a href="{{route('scan.print', [$sample->id_samples,$samplelab->id_laboratorium])}}">                   
                                                        <button type="button" class="btn btn-success btn-rounded ">
                                                            <i class="fa fa-eyes"></i> Lihat Hasil                                                 
                                                        </button> 
                                                     </a>
                                                     @elseif(isset($samplelab->verifikasi_hasil_date))
                                                     <button type="button" class="btn btn-warning btn-rounded ">
                                                        <i class="fa fa-spinner"></i> Sedang Proses                                                 
                                                     </button> 
                                                     @else

                                                     <button type="button" class="btn btn-secondary btn-rounded" disabled><i class="fa fa-spinner"></i> Belum Proses</button>
                                                   
                                                     @endif
                                                 @else
                                                 <button type="button" class="btn btn-secondary btn-rounded" disabled><i class="fa fa-spinner"></i> Belum Proses</button>
                                                   
                                                 @endif
                                             </td>
                                         </tr>
                                       
                                     </thead>
                                    
                                 </table>
                                 <br>
                                 <br>


                             </div>
                         </div>
                     </div>
                     <br>
                </div>
                <br>
                @endforeach
                </div>
            </div>
            
        </div>
    @endforeach

    




@endsection

@section('scripts')
    <script>
    </script>
@endsection





