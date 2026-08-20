@php
    $menu_id = SmtHelp::get_menuid();
    $linkmenu = SmtHelp::get_linkmenu();
    $link = request()->segment(3);
    $get_artikel = \Smt\Masterweb\Models\Content::where('link_url',$link)->first();
    $artikel = \Smt\Masterweb\Models\Content::orderBy('created_at','DESC')->where([['publish', '1'],['type', '0']])->paginate(6);
    //update views
    $content = \Smt\Masterweb\Models\Content::findOrFail($get_artikel->id_content);
    $content->views = $get_artikel->views+1;
    $content->save();
@endphp
<div class="container p-50-cont">
    <div class="row">
    
      <!-- CONTENT -->
      <div class="col-md-8 blog-main-posts">
    
        <!-- POST ITEM -->
        <div class="blog-post wow fadeIn pb-50">
            <!-- Post Item -->
            <div class="wow fadeIn pb-80">
                <!-- <div class="post-prev-img">
                    <a href="#"><img class="lebar" src="{{asset('assets/public/images/'.$get_artikel->img_thumbnail)}}" alt="img"></a>
                </div> -->
                {{-- <div class="post-prev-info">
                    
                </div> --}}
                <div class="post-prev-title">
                    <span class="gotham-medium-red"><span class="bold label label-primary">Blog</span> {{  SmtHelp::fdate(explode(' ',$get_artikel->created_at)[0],"DDMMYYYY") }} / {{ $get_artikel->author }}</span>
                    <h1 class="gotham-medium-black bold mt-10" style="margin: 0px;"><a href="#">{{ $get_artikel->title }}</a></h1>
                </div>
               
                <div class="post-prev-text font-blog">
                    <p class="gotham-book">{!! $get_artikel->content !!}</p>
                </div>
                <div class="">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
                    <p class="mt-10 gotham-light-black">Bagikan ke sosial media :</p>
                    <div class="footer-2-soc-a" style="margin-left:-10px;">
                        <a href="https://api.whatsapp.com/send/?text=Nama%20Info%20%3A{{ ucfirst($get_artikel->title)}} %20%0A {{ Request::url() }}" title="Whatsapp" target="_blank"><button class="btn-sosmed btn-share-whatsapp"><i class="fa fa-whatsapp"></i></button></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ Request::url() }} " title="Facebook" target="_blank"><button class="btn-sosmed btn-share-facebook"><i class="fa fa-facebook"></i></button></a>
                        <a href="https://twitter.com/intent/tweet?url={{ Request::url() }}  " title="Twitter" target="_blank"><button class="btn-sosmed btn-share-twitter"><i class="fa fa-twitter"></i></button></a>
                        <a href="https://social-plugins.line.me/lineit/share?url={{ Request::url() }}  " title="Line" target="_blank"><button class="btn-sosmed btn-share-google"><i class="fa fa-weixin"></i></button></a>
                    </div>
                </div>
                {{-- comment --}}
        <div id="fb-root"></div>

            <script>(function(d, s, id) {

            var js, fjs = d.getElementsByTagName(s)[0];

            if (d.getElementById(id)) return;

            js = d.createElement(s); js.id = id;

            js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";

            fjs.parentNode.insertBefore(js, fjs);

            }(document, 'script', 'facebook-jssdk'));</script>

        <div class="fb-comments" data-href="http://localhost:8000/info/{{ request()->segment(2) }}" data-num-posts="2" data-width="100%" ></div>
        {{-- comment --}}
            </div>
      </div>
      </div>
    </div>
</div>
