(function($) {
  'use strict';

  // Hover expand hanya saat sidebar mode icon-only (desktop).
  $(document).on('mouseenter.smtSidebarHover', '.sidebar .nav > .nav-item', function() {
    if (!$('body').hasClass('sidebar-icon-only')) {
      return;
    }
    if ($(this).hasClass('nav-profile')) {
      return;
    }
    $(this).addClass('hover-open');
  });

  $(document).on('mouseleave.smtSidebarHover', '.sidebar .nav > .nav-item', function() {
    $(this).removeClass('hover-open');
  });
})(jQuery);
