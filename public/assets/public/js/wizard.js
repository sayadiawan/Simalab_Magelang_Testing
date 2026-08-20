(function($) {
  'use strict';
  var form = $("#example-form");
  form.children("div").steps({
    headerTag: "h3",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    onFinished: function(event, currentIndex) {
      alert("Submitted!");
    }
  });
  var validationForm = $("#example-validation-form");
  validationForm.val({
    errorPlacement: function errorPlacement(error, element) {
      element.before(error);
    },
    rules: {
      confirm: {
        equalTo: "#password"
      }
    }
  });
  validationForm.children("div").steps({
    headerTag: "h3",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    onStepChanging: function(event, currentIndex, newIndex) {
      validationForm.val({
        ignore: [":disabled", ":hidden"]
      })
      return validationForm.val();
    },
    onFinishing: function(event, currentIndex) {
      validationForm.val({
        ignore: [':disabled']
      })
      return validationForm.val();
    },
    onFinished: function(event, currentIndex) {
      alert("Submitted!");
    }
  });
  var verticalForm = $("#example-vertical-wizard");
  verticalForm.children("div").steps({
    headerTag: "h3",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    stepsOrientation: "vertical",
    onFinished: function(event, currentIndex) {
      alert("Submitted!");
    }
  });
  var dynamic = $('#dynamic-custom');
  dynamic.children("div").steps({
    headerTag: "h3",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    enableFinishButton: false,
    onInit: function (event, current) {
      $('.actions > ul > li:first-child').attr('style', 'display:none');
    },
    onStepChanged: function (event, current, next) {
        if (current < 0) {
            $('.actions > ul > li:first-child').attr('style', '');
        } else {
            $('.actions > ul > li:first-child').attr('style', 'display:none');
        }
    },
    labels: {
        finish: 'Sign Up <i class="fa fa-chevron-right"></i>',
        next: 'Selanjutnya <i class="fa fa-chevron-right"></i>',
        previous: '<i class="fa fa-chevron-left"></i> Previous'
    }
  }); 
})(jQuery);