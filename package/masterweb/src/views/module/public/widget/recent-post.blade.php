@php
$linkmenu = SmtHelp::get_linkmenu();
$artikel = \Smt\Masterweb\Models\Content::orderBy('created_at','DESC')->where([['publish', '1'],['type', '0']])->paginate(5);
@endphp
<div class="page-section p-50-cont">
<div class="widget">
<h5 class="widget-title">Artikel Terbaru</h5>
        <div class="widget-body">
            <ul class="clearlist widget-posts">
                @foreach ($artikel as $content)
                <li class="clearfix">
                    <a href="{{ url("$linkmenu/view/".$content->link_url) }}"><img src="{{SmtHelp::img_empty('assets/public/images/',$content->img_thumbnail)}}" class="widget-posts-img" alt="img"></a>
                    <div class="widget-posts-descr font-blog">
                        <a href="{{ url("$linkmenu/view/".$content->link_url) }}">{{$content->title}}</a>
                        {{ SmtHelp::fdate(explode(' ',$content->created_at)[0],"DDMMYYYY") }}<span class="slash-divider">/</span>{{ $content->author }}
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
