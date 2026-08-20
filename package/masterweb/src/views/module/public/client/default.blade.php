@php
$data = Smt\Masterweb\Models\Client::orderBy('urutan', 'asc')->get();
@endphp
<div class="page-section p-110-cont" style="background-image: url(http://zaib.sandbox.etdevs.com/divi/wp-content/uploads/sites/2/2020/01/img-02.png);background-repeat: no-repeat !important;background-size: cover !important;background-position: center center !important;">
    <div class="tengah">
        <h2 class="no-margin">
            <span class="font-light ml-20 blue1 text-center">Klien Kami</span>
        </h2>
        <h4 class="no-margin">
            <p>Beberapa klien yang mempercayakan jasa Teknologi Informasi (IT) kepada kami </p>
        </h4>
    </div>
    <div class="garis" style="margin-bottom: 50px;"></div>
    <section class="client">
        <div class="container">
            <div class="row">
                <div class="row client-row">
                    @foreach($data as $item)
                    <div class="col-xs-6 col-md-2 col-sm-4 text-center">
                        <img alt="client" class="img-client" src="{{asset('assets/public/images/client/'.$item->file_client)}}">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

</div>