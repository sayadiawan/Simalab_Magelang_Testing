@php
$link = str_replace('-',' ',request()->segment('3'));
$get_data = Smt\Masterweb\Models\Portofolio::where('name_portofolio',$link)->first();

//loop
$data_cat = Smt\Masterweb\Models\CategoryPortofolio::orderBy('name_category_portofolio', 'asc')->get();
$data = Smt\Masterweb\Models\Portofolio::where([['publish','=','1'],['name_portofolio','<>',$link]])->orderBy('created_at','DESC')->paginate(4);

    $tech = explode(',',$get_data->tech_portofolio);
    @endphp

    <div class="p-140-cont">

        <!-- PORTFOLIO INFO -->
        <div class="container">
            <div class="row">

                <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- PROJECT DETAIL -->
                    <div class="port-detail-cont mb-30">
                        <h4 class="blog-page-title mt-0 mb-20">DETAIL PORTOFOLIO</h4>
                        <div class="port-detail">
                            <h5>
                                <p>
                                    <strong>Klien</strong> : {{ strtoupper($get_data->client_portofolio) }}
                                </p>

                                <p>
                                    <strong>Kategori </strong> :
                                    <a href="/portofolio/cat/{{ $get_data->category_portofolio->link_category_portofolio }}">{{ strtoupper($get_data->category_portofolio->name_category_portofolio) }}</a>
                                </p>
                                <p>
                                    <strong>Url </strong> :
                                    <a href="{{ $get_data->link_portofolio }}" target="_blank">{{ $get_data->link_portofolio}}</a>
                                </p>
                                <p>
                                    <strong>Teknologi yang Digunakan</strong> :
                                    <div class="tags">
                                        @foreach ($tech as $item)
                                        <a href="#">{{ $item }}</a>
                                        @endforeach
                                    </div>

                                </p>
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xs-12 col-sm-6 mb-80">
                    <div class="fullwidth-slider owl-carousel owl-arrows-bg owl-dark-bg owl-pag-2">

                        <!-- ITEM -->
                        <div class="item m-0">
                            <div>
                                <img alt="about us" src="{{asset('assets/public/images/portofolio/'.$get_data->file_portofolio)}}">
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- CAROUSEL -->
        <div class="container mb-80">
            {!! $get_data->desc_portofolio !!}
        </div>

        <!-- RELATED PORT ITEMS -->
        <div class="page-section mt-80 mb-0">
            <div class="container">

                <h4 class="blog-page-title mt-0 mb-40">PORTOFOLIO LAINNYA</h4>
                <ul class="port-grid display-hover-on-mobile port-grid-3 port-grid-gut clearfix" id="items-grid" style="position: relative; height: 564px;">

                    <!-- Item 1 -->
                    @foreach ($data as $item)
                    <li role="button" class="port-item mix development" style="position: absolute; left: 0px; top: 0px;">
                        <a href="/portofolio/view/{{ str_replace(' ','-',$item->name_portofolio) }}">
                            <div class="port-img-overlay"><img class="port-main-img" src="{{asset('assets/public/images/portofolio/'.$item->file_portofolio)}}" alt="{{ $item->name_portofolio }}"></div>
                        </a>
                        <div class="port-overlay-cont">

                            <div class="port-title-cont">
                                <h3><a href="/portofolio/view/{{ str_replace(' ','-',$item->name_portofolio) }}">{{ strtoupper($item->name_portofolio) }}</a></h3>
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