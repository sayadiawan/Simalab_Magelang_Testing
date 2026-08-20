<!DOCTYPE html>
  <html lang="en">
      @include('masterweb::template.metadata')
  <body>
  <div class="container-scroller">
      @include('masterweb::template.header')
    <div class="container-fluid page-body-wrapper">
        @include('template.asside')
      <div class="main-panel">
        <div class="content-wrapper">
            @yield('content')
        </div>
        <footer class="footer">
            @include('template.footer')
        </footer>
      </div>
    </div>
  </div>
  @include('template.scripts')
  </body>

  </html>