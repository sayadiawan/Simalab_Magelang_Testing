@php
$menu_id = SmtHelp::get_menuid(18);
$linkmenu = SmtHelp::get_linkmenu(18);
$artikel = \Smt\Masterweb\Models\Content::orderBy('created_at','DESC')->where([['publish', '1'],['type', '0'],['menu_id',$menu_id]])->paginate(3);
@endphp
<div class="page-section pt-100-b-80-cont">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="clearfix">
                    <div class="left">
                        <h2 class="section-title-2 text-center pr-0"><span class="bold blue-blog">BERITA TERBARU</span></h2>
                        <div class="garis-blog"></div>
                    </div>
                    <div class="right">
                        <a class="button small blue right-1" href="{{$linkmenu}}">Berita Selengkapnya</a>
                    </div>
                </div>
                @foreach ($artikel as $content)
                <div class="col-sm-12 col-md-4 col-lg-4 pb-50">
                    <div class="post-prev-img">
                        <a href="{{ url("$linkmenu/view/".$content->link_url) }}"><img src="{{SmtHelp::img_empty('assets/public/images/',$content->img_thumbnail)}}" alt="img"></a>
                    </div>

                    <div class="post-prev-title">
                        <h3><a href="{{ url("$linkmenu/view/".$content->link_url) }}">{{$content->title}}</a></h3>
                    </div>

                    <div class="post-prev-info">
                        {{ SmtHelp::fdate(explode(' ',$content->created_at)[0],"DDMMYYYY") }}<span class="slash-divider">/</span>{{ $content->author }}
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>