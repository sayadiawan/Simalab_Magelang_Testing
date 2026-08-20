@php
$menu_id = SmtHelp::get_menuid();
$linkmenu = SmtHelp::get_linkmenu();
$artikel = \Smt\Masterweb\Models\Content::orderBy('created_at','DESC')->where([['publish', '1'],['type', '0'],['menu_id',$menu_id],['title', 'like', '%'.request()->get('q').'%']])->paginate(12);
@endphp
<div class="container p-140-cont">
    <div class="row">
        <!-- CONTENT -->
        <div class="col-sm-12 blog-main-posts">
            <div class="">
                @foreach ($artikel as $content)
                <div class="col-sm-4 col-md-4 col-lg-4 pb-50">
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
            <center>
                <div class="row">
                    <div class="col-md-12 mb-30">
                        {{ $artikel->links("masterweb::template.public.pagination") }}
                    </div>
            </center>
        </div>

        {{-- <!-- SIDEBAR -->
        <div class="col-sm-4 col-md-3 col-md-offset-1">

            <!-- SEARCH -->
            <div class="">
                <form class="form-search widget-search-form" action="" method="get">
                    <input type="text" name="q" class="input-search-widget" placeholder="Search">
                    <button class="" type="submit" title="Start Search">
                        <span aria-hidden="true" class="icon_search"></span>
                    </button>
                </form>
            </div>

            <!-- WIDGET -->
            <div class="">

                <h5 class="widget-title">Berita Terbaru</h5>

                <div class="widget-body">
                    <ul class="clearlist widget-posts">
                        <li class="clearfix">
                            <a href=""><img src="https://sevenmediatech.co.id//images/article/tips_desain_web_agar_terlihat__profesional.jpg?1594865725210" alt="" class="widget-posts-img"></a>
                            <div class="widget-posts-descr">
                                <a href="#" title="">TIME FOR MINIMALISM</a>
                                <div>21 Jule<span class="slash-divider">/</span> John Doe</div>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div> --}}

    </div>
</div>