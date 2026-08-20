@php
    $link = str_replace('-',' ',request()->segment('3'));
    $linkmenu = SmtHelp::get_linkmenu();
    $get_data = Smt\Masterweb\Models\Layanan::where('title',$link)->first();
    $random_data = Smt\Masterweb\Models\Layanan::inRandomOrder()->limit(4)->get();

    $fitur = explode(',' , $get_data->fitur);
    $categories = Smt\Masterweb\Models\CategoryLayanan::all();

    $artikels = \Smt\Masterweb\Models\Content::inRandomOrder()->limit(3)->get();
    
@endphp
<div class="container  pt-110-b-0-cont">
    <div class="row">
        <!-- CONTENT -->
        <div class="col-sm-8 blog-main-posts pt-50">

            <!-- Post Item -->
            <div class="wow fadeIn">

                <div class="post-prev-title">
                    <h1 class="bold text-center">{{$get_data->title}}</h1>
                </div>

                <div>
                    <h4>
                        <p>{{$get_data->deskripsi}}
                        </p>
                    </h4>
                    <div id="pricing-tables" class="bs-docs-section">
                        <div class="row mb-50">
                            <div class="pricing-table clearfix">
                                <div class="col-md-2 col-sm-2 col-xs-12"></div>
                                <div class="col-md-8 col-sm-8 col-xs-12 mb-30">
                                    <div>
                                        <h2 class="pt-h1-container">FITUR WEBSITE</h2>
                                        <div class="pt-content-container">
                                            <ul>
                                                @foreach ($fitur as $item)
                                                    <li><i class="fa fa-check mr-5"></i>{{$item}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-2 col-xs-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- SIDEBAR -->
        <div class="col-sm-3 col-md-3 pt-50">

            <!-- WIDGET -->
            <div class="widget">

                <h5 class="widget-title">Kategori Produk</h5>

                <div class="widget-body">
                    <ul class="clearlist widget-menu">
                        @foreach ($categories as $category)
                            @php
                                $jml = Smt\Masterweb\Models\Layanan::where('kategori',$category->id_category_layanan)->count();
                            @endphp
                            <li>
                                <a href="#" title="">{{$category->nama_layanan}}</a><small><span class="slash-divider">/</span> {{$jml}}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>

            <!-- WIDGET -->
            <div class="widget">

                <h5 class="widget-title">Blog</h5>

                <div class="widget-body">
                    <ul class="clearlist widget-posts">
                        @foreach ($artikels as $artikel)
                            <li class="clearfix">
                                <a href=""><img src="{{SmtHelp::img_empty('assets/public/images/',$artikel->img_thumbnail)}}" alt="" class="widget-posts-img"></a>
                                <div class="widget-posts-descr">
                                    <a href="#" title="">{{$artikel->judul}}</a>
                                    <div>{{ SmtHelp::fdate(explode(' ',$artikel->created_at)[0],"DDMM") }}<span class="slash-divider">/</span> {{$artikel->author}}</div>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </div>

            </div>



        </div>
        {{-- Produk Terkait --}}
        
    </div>
</div>
<div class="page-section grey-dark-bg  nl-cont">
    <div class="container">
        <div class="relative">
            <div class="container row">
                <div class="col-md-8">
                    <h3 style="margin:5px 0 16px 0;">
                        <b>
                            "Untuk informasi lebih lanjut, Anda dapat menghubungi langsung"
                        </b>
                    </h3>
                </div>
                <div class="col-md-4">
                    <a class="button small gray" href="https://api.whatsapp.com/send?phone=6285747747725&text=Halo,Seven%20Media%20Technology"></i>LIVE DEMO</a>
                    <a class="button small teal" href="https://api.whatsapp.com/send?phone=6285747747725&text=Halo,Seven%20Media%20Technology"><i class="fa fa-whatsapp mr-5"></i>HUBUNGI KAMI</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-section pt-70">
    <div class="container">
        <div class="row">
            <div class="mb-20">
                <h2 class="section-title-2 pr-0"><span class="bold">PRODUK TERKAIT</span></h2>
              </div>
            <div class="col-md-12 pb-50">
                <div class="row">
                    @foreach ($random_data as $data)  
                        <div class="col-md-3 col-sm-6 col-xs-12 mb-10 shop-dep-item">
                            <a href="{{ url("$linkmenu/view/$data->link_url") }}">
                                <img src="{{asset('assets/public/images/layanan/'.$data->image)}}" alt="img">
                                <div class="shop-dep-text-cont">
                                    <a href="{{ url("$linkmenu/view/$data->link_url") }}">
                                        <h5>{{$data->title}}</h5>
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
</div>