@php
    $faq = DB::table('tb_faq')->orderBy('ordered', 'ASC')->get();
@endphp
<div class="page-section p-50-cont" style="background-image: url(http://zaib.sandbox.etdevs.com/divi/wp-content/uploads/sites/2/2020/01/img-02.png);background-repeat: no-repeat !important;background-size: cover !important;background-position: center center !important;">
    <div class="tengah">
        <h2 class="no-margin">
            <span class="font-light ml-20 blue1 text-center">Frequently Asked Questions
            </span>
        </h2>
        <h4 class="no-margin">
            <p>Frequently Asked Questions :
            </p>
        </h4>
    </div>
    <div class="garis"></div>
    <div id="accordions" class="bs-docs-section mb-100">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">

                    <!-- Accordion -->
                    <dl class="accordion">
                        @foreach ($faq as $dfaq)
                        <dt>
                            <a href="#">{{$dfaq->question}}</a>
                        </dt>
                        <dd>
                            {{$dfaq->answer}}
                        </dd>
                        @endforeach
                    </dl>
                    <!-- End Accordion -->
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </div>
    </div>