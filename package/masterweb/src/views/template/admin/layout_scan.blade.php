<!DOCTYPE html>
    <html lang="en">
        @include('masterweb::template.admin.metadata')
        <body onload="startTime()">
        <div class="container-scroller">

          @include('masterweb::template.admin.header_scan')
            <div class="container-fluid  page-body-wrapper ">
              <div class="main-panel" style="width: 100%; height: 100%" >
               
                  <div class="content-wrapper">
                    <div ><span id="txt"></span></div>
                      @yield('content')
                  </div>
                  <footer class="footer">
                    @include('masterweb::template.admin.footer')
                </footer>
              </div>
            </div>
        </div>
        @include('masterweb::template.admin.scripts')
        @yield('scripts')

        </body>

    </html>
