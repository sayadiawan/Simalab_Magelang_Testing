@php
    //sesi 2 categori
    $sesi = Request::segment(2);
    //sesi 3 url category portofilio
    $cat = Request::segment(3);
    
    if (empty($sesi)) {
        $data = Smt\Masterweb\Models\Portofolio::where('publish','1')->orderBy('created_at','DESC')->paginate(9);
    } else {
        $data = Smt\Masterweb\Models\Portofolio::where('publish','1')->whereHas('category_portofolio',function ($qw) use ($cat)
        {
            $qw->where('link_category_portofolio',$cat);
        })->orderBy('created_at','DESC')->paginate(9);
    }
  
    $data_cat = Smt\Masterweb\Models\CategoryPortofolio::orderBy('created_at', 'ASC')->get();
    
@endphp
<div class="page-section p-50-cont">
    <div class="">
        <div class="row">
            <div class="container">
                <div class="tengah">
                    <h2 class="no-margin">
                        <span class="font-light ml-20 blue1 text-center">Portofolio Kami
                        </span>
                    </h2>
                    <h4 class="no-margin">
                        <p>Ratusan Pekerjaan yang telah kami kerjakan dari pembuatan website hingga implementasi sistem informasi.

                        </p>
                    </h4>
                </div>
        </div>
            <div class="garis"></div>
            <div class=" plr-30 plr-0-767 clearfix">
                <!-- COTENT CONTAINER -->
                <div class="plr-30 pt-30 pb-20">

                    <div class="relative">
                        <!-- PORTFOLIO FILTER -->
                        <div class="port-filter text-center text-left-767">
                            <a href="#" class="filter button small gray-light" data-filter="*">Semua Portofolio</a>
                            @foreach ($data_cat as $item)

                                @php
                                    if($item->link_category_portofolio==$cat){
                                        $set_cat = "active";
                                    }else{
                                        $set_cat = null;
                                    }
                                @endphp

                                <a href="#" class="filter button small gray-light {{ $set_cat }}" data-filter=".{{ $item->name_category_portofolio }}">{{ $item->name_category_portofolio }}</a>
                            @endforeach
                        </div>

                        <!-- ITEMS GRID -->

                        <ul class="port-grid port-grid-gut port-grid-3 clearfix row" id="items-grid">
                            <!-- Item 1 -->
                            @foreach ($data as $item)
                                <li class="port-item mix  {{ $item->category_portofolio->name_category_portofolio }}">
                                    <a href="/portofolio/view/{{ str_replace(' ','-',$item->name_portofolio) }}">
                                        <div class="port-img-overlay"><img class="port-main-img" src="{{asset('assets/public/images/portofolio/'.$item->file_portofolio)}}" alt="{{ $item->name_portofolio }}"></div>
                                    </a>
                                    <div class="port-overlay-cont">

                                        <div class="port-title-cont">
                                        <h3><a href="/portofolio/view/{{ str_replace(' ','-',$item->name_portofolio) }}">{{  strtoupper($item->name_portofolio) }}</a></h3>
                                            <span class="label label-primary">
                                                <a href="#">{{ ucfirst($item->category_portofolio->name_category_portofolio) }}</a>
                                            </span>

                                        </div>
                                        <div class="port-btn-cont">
                                            <a href="{{asset('assets/public/images/portofolio/'.$item->file_portofolio)}}" class="lightbox mr-20">
                                                <div aria-hidden="true" class="icon_search"></div>
                                            </a>
                                            <a href="/portofolio/view/{{ str_replace(' ','-',$item->name_portofolio) }}">
                                                <div aria-hidden="true" class="icon_link"></div>
                                            </a>
                                        </div>

                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <center>
                            <div class="row">

                                <div class="col-md-12 mb-30">
                                    {{ $data->links("masterweb::template.public.pagination") }}
                                </div>
                        </center>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>