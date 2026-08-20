@php
//get setting option
$option = \Smt\Masterweb\Models\Option::first();
//get sosial media
$socmed = \Smt\Masterweb\Models\Socmed::all()->where('publish','1');
//get contact
$contact = \Smt\Masterweb\Models\Contact::first();
@endphp

        <!-- FOOTER 2 -->
        <footer id="footer2" class="page-section pt-80 pb-50 bg-footer">
          <div class="container">
            <div class="row">
                <center>
                    <img src="https://v3.sevenmediatech.co.id/assets/public/images/logo/LOGO%20SMT%20New%20White.png" width="20%" alt="">
                </center>
                <blockquote class="quote mt-20 mb-40 m-p-0 text-center white">
                    <h3 class="font-white"><b>“If your Business is not on the Internet, then your Business will be out of business”</b></h3>
                          <footer class="font-white">Bill Gates, Founder Microsoft.</footer>
                </blockquote>        
                
                <div class="mt-20 mb-40 text-center">
                    <a class="button medium thin hover-dark tp-button white" href="https://api.whatsapp.com/send?phone=6285747747725&amp;text=Halo,Seven%20Media%20Technology"><i class="fa fa-whatsapp mr-5"></i>HUBUNGI KAMI</a>
                </div>
            </div>    
            
            <div class="footer-2-copy-cont clearfix">
              <!-- Social Links -->
              <div class="footer-2-soc-a right">
                @foreach ($socmed as $socmed)
                <a href="{{$socmed->link}}" class="font-white" title="{{$socmed->name}}" target="_blank">
                        <span aria-hidden="true" class="{{$socmed->icon}}"></span>
                    </a>
                {{-- <a href="{{$socmed->link}}" target="_blank"><i class="{{$socmed->icon}} btn-white"></i></a> --}}
                @endforeach
               
              </div>
              <!-- Copyright -->
              <div class="left">
                <a class="footer-2-copy fnt-size font-white" href="/tentang-kami" target="_blank">Tentang Kami</a>
                <a class="footer-2-copy fnt-size font-white" href="/faq" target="_blank">Faq</a>
                <a class="footer-2-copy fnt-size font-white" href="/kontak" target="_blank">Kontak</a>
              </div>
              <div class="text-center">
                    <span class="font-white">
                        <a href="#" class="font-white">{{ $option->footer}}</a>
                    </span>
              </div>
            </div>
                    
          </div>
        </footer>
        <a target="_blank" href="https://api.whatsapp.com/send?phone=<?= str_replace('-','',$contact->phone) ?>&text=" class="whatsapp-button"><i class="fa fa-whatsapp"></i></a>