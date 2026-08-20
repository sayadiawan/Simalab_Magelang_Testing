<?php
//sesi 2 categori
$sesi = Request::segment(2);
//sesi 3 url category portofilio
$cat = Request::segment(3);
// dd($sesi);

$menu_id = SmtHelp::get_menuid();

$linkmenu = SmtHelp::get_linkmenu();
$link = request()->segment(1);
$getServices = \Smt\Masterweb\Models\Content::where('menu_id', $menu_id)->first();
if (empty($sesi)) {
    $get_layanan = \Smt\Masterweb\Models\Layanan::where('menu_id', $menu_id)->get();
} else {
    $get_layanan = Smt\Masterweb\Models\Layanan::where('menu_id', $menu_id)->whereHas('category_layanan', function ($qw) use ($cat) {
        $qw->where('link_category_layanan', $cat);
    })->orderBy('created_at', 'DESC')->paginate(9);
}

$data_cat = Smt\Masterweb\Models\CategoryLayanan::orderBy('nama_layanan', 'ASC')->get();
echo $cat;

if (empty($getServices)) {
?>
    <div class=" page-section p-80-cont">
        <div class="container">
            <div class="alert alert-danger nobottommargin">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <span aria-hidden="true" class="alert-icon icon_blocked"></span><strong>Perhatian</strong> Konten Belum di Isi, Silahkan Isi Konten Terlebih Dahulu
            </div>
        </div>
    </div>
<?php
} else {
?>
    <!-- FEATURES 4 -->
    <div class="page-section p-50-cont">
        <div class="container">
            <div class="post-prev-text font-blog">
                <p class="gotham-book font-blog"> {!!$getServices->content!!}</p>
            </div>
        </div>
    </div>
<?php
}
?>

@if (!empty($get_layanan->first()))
<div class=" plr-30 plr-0-767 clearfix">
    <!-- COTENT CONTAINER -->
    <div class="plr-30  pb-20">

        <div class="relative">
            <div class="port-filter text-center text-left-767">
                <a href="#" class="filter button small gray-light" data-filter="*">Semua </a>
                @foreach ($data_cat as $item)

                @php
                if($item->link_category_layanan==$cat){
                $set_cat = "active";
                }else{
                $set_cat = null;
                }
                @endphp

                <a href="#" class="filter button small gray-light {{ $set_cat }}" data-filter=".{{ $item->nama_layanan }}">{{ $item->nama_layanan }}</a>
                @endforeach
            </div>
            <!-- PORTFOLIO FILTER -->

            <!-- ITEMS GRID -->

            <div class="clearfix row" id="items-grid">
                <!-- Item 1 -->

                <div class="col-md-12 pb-50">
                    <div class="row">
                        @foreach ($get_layanan as $item)
                        <div class="col-md-3 col-sm-6 col-xs-6 mb-10 shop-dep-item {{ $item->category_layanan->nama_layanan }}">
                            <a href="{{ url("$linkmenu/view/$item->link_url") }}">
                                <img src="{{asset('assets/public/images/layanan/'.$item->image)}}" alt="img">
                                <div class="shop-dep-text-cont">
                                    <a href="{{ url("$linkmenu/view/$item->link_url") }}">
                                        <h5>{{$item->title}}</h5>
                                    </a>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endif