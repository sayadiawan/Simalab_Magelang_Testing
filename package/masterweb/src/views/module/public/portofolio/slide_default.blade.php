@php
$data = Smt\Masterweb\Models\Portofolio::where('publish','1')->orderBy('created_at','DESC')->paginate(5);
$n = count($data);
if($n%2==0) //Jika banyak-nya data  genap
    {
        $d1=$n/2;
        $d2=$d1+1;
        $nilai_median=$d1+($d2-$d1)/2;
    }
else   //Jika banyak-nya data  ganjil
    {
        $dt=($n+1)/2;
        $nilai_median=$dt;
    }
@endphp
<div class="page-section p-110-cont">
    <div class="container">
        <div class="tengah">
            <h2 class="no-margin">
                <span class="font-light ml-20 blue1 text-center">Portofolio Kami</span>
            </h2>
            <h4 class="no-margin">
                <p>
                    Ratusan Pekerjaan yang telah kami kerjakan dari pembuatan website hingga implementasi sistem informasi.
                </p>
            </h4>
        </div>
        <div class="garis"></div>
        <div class="row">
            <div class="container">
                <!-- Swiper -->
                
                <div class="swiper-container two">
                    <div class="swiper-wrapper">
                    @foreach ($data as $item)
                        <div class="swiper-slide">
                            <div class="slider-image img-responsive">
                                <a href="/portofolio/view/{{ str_replace(' ','-',$item->name_portofolio) }}">
                                    <img src="{{asset('assets/public/images/portofolio/'.$item->file_portofolio)}}" alt="{{  strtoupper($item->name_portofolio) }}">
                                </a>
                            </div>
                        </div>
                        @endforeach
                        <!-- Add Pagination -->
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <div class="tengah mt-30">
                    <a class="button medium blue" href="/portofolio">Lihat Portfolio Lain</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/public/js/swipper/swipper.min.js')}}"></script>

<script>
    var swiper = new Swiper('.swiper-container.two', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        effect: 'coverflow',
        loop: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        coverflow: {
            rotate: 0,
            stretch: 100,
            depth: 150,
            modifier: 1.5,
            slideShadows: false,
        }
    });
</script>