<script type="text/javascript">
    // SCRIPT WINDOW SCROLLED
    $(window).scroll(function() {
        if ($(window).scrollTop() > 1) {
            $('.top-bar').fadeOut('fast');
            $('.container clearfix').css("margin-top", "-35px");
            $('.top-bar-section left').css("width", "50%");
            $('.top-bar-section right display-no-xxs').css("margin", "30px 60px 0 20px");
        } else {
            $('.top-bar').fadeIn('slow');
            $('container clearfix').css("margin-top", "0px");
            $('.top-bar-section left').css("width", "80%");
            $('#top-bar-section right display-no-xxs').css("margin", "45px 60px 0 20px");
        }
    });
</script>
<script>
    const article = document.querySelector('article');

// to compute the center of the card retrieve its coordinates and dimensions
const {
  x, y, width, height,
} = article.getBoundingClientRect();
const cx = x + width / 2;
const cy = y + height / 2;

// following the mousemove event compute the distance betwen the cursor and the center of the card
function handleMove(e) {
  const { pageX, pageY } = e;

  // ! consider the relative distance in the [-1, 1] range
  const dx = (cx - pageX) / (width / 2);
  const dy = (cy - pageY) / (height / 2);

  // rotate the card around the x axis, according to the vertical distance, and around the y acis, according to the horizontal gap 
  this.style.transform = `rotateX(${10 * dy * -1}deg) rotateY(${10 * dx}deg)`;
}

// following the mouseout event reset the transform property
function handleOut() {
  this.style.transform = 'initial';
}

article.addEventListener('mousemove', handleMove);
article.addEventListener('mouseout', handleOut);

</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>

<!-- jQuery  -->
<script type="text/javascript" src="{{ asset('assets/public/js/jquery-1.11.2.min.js')}}"></script>
<!-- jQuery  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bxslider/4.2.5/jquery.bxslider.js"></script>

<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="{{ asset('assets/public/js/bootstrap.min.js')}}"></script>

<!-- MAGNIFIC POPUP -->
<script src='{{ asset('assets/public/js/jquery.magnific-popup.min.js')}}'></script>

<!-- PORTFOLIO SCRIPTS -->
<script type="text/javascript" src="{{ asset('assets/public/js/isotope.pkgd.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/public/js/imagesloaded.pkgd.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/public/js/masonry.pkgd.min.js')}}"></script>

<!-- COUNTER -->
<script type="text/javascript" src="{{ asset('assets/public/js/jquery.countTo.js')}}"></script>

<!-- APPEAR -->
<script type="text/javascript" src="{{ asset('assets/public/js/jquery.appear.js')}}"></script>

<!-- OWL CAROUSEL -->
<script type="text/javascript" src="{{ asset('assets/public/js/owl.carousel.min.js')}}"></script>

<!-- MAIN SCRIPT -->
<script src="{{ asset('assets/public/js/main.js')}}"></script>

<!-- FLEX SLIDER SCRIPTS  -->
<script src="{{ asset('assets/public/js/jquery.flexslider-min.js')}}"></script>
<script src="{{ asset('assets/public/js/flex-slider.js')}}"></script>
<script src="{{ asset('assets/public/js/chart.js')}}"></script>

<!-- SLIDER REVOLUTION 4.x SCRIPTS  -->
<script src="{{ asset('assets/public/rs-plugin/js/jquery.themepunch.tools.min.js') }}"></script>
<script src="{{ asset('assets/public/rs-plugin/js/jquery.themepunch.revolution-parallax.min.js') }}"></script>
<script>
    ///java testimoni
    // vars
    'use strict'
    var testim = document.getElementById("testim"),
        testimDots = Array.prototype.slice.call(document.getElementById("testim-dots").children),
        testimContent = Array.prototype.slice.call(document.getElementById("testim-content").children),
        testimLeftArrow = document.getElementById("left-arrow"),
        testimRightArrow = document.getElementById("right-arrow"),
        testimSpeed = 4500,
        currentSlide = 0,
        currentActive = 0,
        testimTimer,
        touchStartPos,
        touchEndPos,
        touchPosDiff,
        ignoreTouch = 30;;

    window.onload = function() {

            // Testim Script
            function playSlide(slide) {
                for (var k = 0; k < testimDots.length; k++) {
                    testimContent[k].classList.remove("active");
                    testimContent[k].classList.remove("inactive");
                    testimDots[k].classList.remove("active");
                }

                if (slide < 0) {
                    slide = currentSlide = testimContent.length - 1;
                }

                if (slide > testimContent.length - 1) {
                    slide = currentSlide = 0;
                }

                if (currentActive != currentSlide) {
                    testimContent[currentActive].classList.add("inactive");
                }
                testimContent[slide].classList.add("active");
                testimDots[slide].classList.add("active");

                currentActive = currentSlide;

                clearTimeout(testimTimer);
                testimTimer = setTimeout(function() {
                    playSlide(currentSlide += 1);
                }, testimSpeed)
            }

            testimLeftArrow.addEventListener("click", function() {
                playSlide(currentSlide -= 1);
            })

            testimRightArrow.addEventListener("click", function() {
                playSlide(currentSlide += 1);
            })

            for (var l = 0; l < testimDots.length; l++) {
                testimDots[l].addEventListener("click", function() {
                    playSlide(currentSlide = testimDots.indexOf(this));
                })
            }

            playSlide(currentSlide);

            // keyboard shortcuts
            document.addEventListener("keyup", function(e) {
                switch (e.keyCode) {
                    case 37:
                        testimLeftArrow.click();
                        break;

                    case 39:
                        testimRightArrow.click();
                        break;

                    case 39:
                        testimRightArrow.click();
                        break;

                    default:
                        break;
                }
            })

            testim.addEventListener("touchstart", function(e) {
                touchStartPos = e.changedTouches[0].clientX;
            })

            testim.addEventListener("touchend", function(e) {
                touchEndPos = e.changedTouches[0].clientX;

                touchPosDiff = touchStartPos - touchEndPos;

                console.log(touchPosDiff);
                console.log(touchStartPos);
                console.log(touchEndPos);


                if (touchPosDiff > 0 + ignoreTouch) {
                    testimLeftArrow.click();
                } else if (touchPosDiff < 0 - ignoreTouch) {
                    testimRightArrow.click();
                } else {
                    return;
                }

            })
        }
        /**********************/
        /*	Client carousel   */
        /**********************/
    $('.carousel-client').bxSlider({
        auto: true,
        slideWidth: 234,
        minSlides: 2,
        maxSlides: 5,
        controls: false
    });
</script>
<!-- SLIDER REVOLUTION INIT  -->
<script>
    jQuery(document).ready(function() {
        if ((navigator.appVersion.indexOf("Win") != -1) && (ieDetect == false)) {
            jQuery('#rs-fullwidth').revolution({
                dottedOverlay: "none",
                delay: 16000,
                startwidth: 1170,
                startheight: 700,
                hideThumbs: 200,

                thumbWidth: 100,
                thumbHeight: 50,
                thumbAmount: 5,

                //fullScreenAlignForce: "off",

                navigationType: "none",
                navigationArrows: "solo",
                navigationStyle: "preview0",

                hideTimerBar: "on",

                touchenabled: "on",
                onHoverStop: "on",

                swipe_velocity: 0.7,
                swipe_min_touches: 1,
                swipe_max_touches: 1,
                drag_block_vertical: false,

                parallax: "scroll",
                parallaxBgFreeze: "on",
                parallaxLevels: [45, 40, 35, 50],
                parallaxDisableOnMobile: "on",

                keyboardNavigation: "off",

                navigationHAlign: "center",
                navigationVAlign: "bottom",
                navigationHOffset: 0,
                navigationVOffset: 20,

                soloArrowLeftHalign: "left",
                soloArrowLeftValign: "center",
                soloArrowLeftHOffset: 20,
                soloArrowLeftVOffset: 0,

                soloArrowRightHalign: "right",
                soloArrowRightValign: "center",
                soloArrowRightHOffset: 20,
                soloArrowRightVOffset: 0,

                shadow: 0,
                fullWidth: "off",
                fullScreen: "on",

                spinner: "spinner4",

                stopLoop: "off",
                stopAfterLoops: -1,
                stopAtSlide: -1,

                shuffle: "off",

                autoHeight: "off",
                forceFullWidth: "off",

                hideThumbsOnMobile: "off",
                hideNavDelayOnMobile: 1500,
                hideBulletsOnMobile: "off",
                hideArrowsOnMobile: "off",
                hideThumbsUnderResolution: 0,

                hideSliderAtLimit: 0,
                hideCaptionAtLimit: 0,
                hideAllCaptionAtLilmit: 0,
                startWithSlide: 0,
                //fullScreenOffsetContainer: ""	
            });
        } else {
            jQuery('#rs-fullwidth').revolution({
                dottedOverlay: "none",
                delay: 16000,
                startwidth: 1170,
                startheight: 760,
                hideThumbs: 200,

                thumbWidth: 100,
                thumbHeight: 50,
                thumbAmount: 5,

                navigationType: "none",
                navigationArrows: "solo",
                navigationStyle: "preview0",

                hideTimerBar: "on",

                touchenabled: "on",
                onHoverStop: "on",

                swipe_velocity: 0.7,
                swipe_min_touches: 1,
                swipe_max_touches: 1,
                drag_block_vertical: false,

                parallax: "mouse",
                parallaxBgFreeze: "on",
                parallaxLevels: [0],
                parallaxDisableOnMobile: "on",

                keyboardNavigation: "off",

                navigationHAlign: "center",
                navigationVAlign: "bottom",
                navigationHOffset: 0,
                navigationVOffset: 20,

                soloArrowLeftHalign: "left",
                soloArrowLeftValign: "center",
                soloArrowLeftHOffset: 20,
                soloArrowLeftVOffset: 0,

                soloArrowRightHalign: "right",
                soloArrowRightValign: "center",
                soloArrowRightHOffset: 20,
                soloArrowRightVOffset: 0,

                shadow: 0,
                fullWidth: "off",
                fullScreen: "on",

                spinner: "spinner4",

                stopLoop: "off",
                stopAfterLoops: -1,
                stopAtSlide: -1,

                shuffle: "off",

                autoHeight: "off",
                forceFullWidth: "off",

                hideThumbsOnMobile: "off",
                hideNavDelayOnMobile: 1500,
                hideBulletsOnMobile: "off",
                hideArrowsOnMobile: "off",
                hideThumbsUnderResolution: 0,

                hideSliderAtLimit: 0,
                hideCaptionAtLimit: 0,
                hideAllCaptionAtLilmit: 0,
                startWithSlide: 0,

            });
        }
    }); //ready
</script>