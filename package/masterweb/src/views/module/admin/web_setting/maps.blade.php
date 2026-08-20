@extends('masterweb::template.admin.layout')

@section('title')
    Maps
@endsection

@section('content')

 
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
                <div class="template-demo">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
                            <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                            <li class="breadcrumb-item"><a href="{{url('/adm-maps')}}">Maps</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><span>Edit</span></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">

      <div class="row">
          
        @if(session('status'))
            <div class="alert alert-success">
                {{session('status')}}
            </div>
        @endif

            <div class="container">
                <h3 class="box-title">GOOGLE MAPS LOCATION</h3>
            </div>
            <style>
              /* Always set the map height explicitly to define the size of the div
               * element that contains the map. */
              #map {
                height: 100%;
              }
              /* Optional: Makes the sample page fill the window. */
              html, body {
                height: 100%;
                margin: 0;
                padding: 0;
              }
              .controls {
                background-color: #fff;
                border-radius: 2px;
                border: 1px solid transparent;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
                box-sizing: border-box;
                font-family: Roboto;
                font-size: 15px;
                font-weight: 300;
                height: 29px;
                margin-left: 17px;
                margin-top: 10px;
                outline: none;
                padding: 0 11px 0 13px;
                text-overflow: ellipsis;
                width: 400px;
              }

              .controls:focus {
                border-color: #4d90fe;
              }
              .title {
                font-weight: bold;
              }
              #infowindow-content {
                display: none;
              }
              #map #infowindow-content {
                display: inline;
              }

            </style>
        <input id="pac-input" class="controls" type="text" placeholder="Search Box">
        <div id="map_canvas" style="height: 400px; width: 100%; border:1px solid #DADADA; margin-bottom:10px;"></div>
        <div class="box-body table-responsive">
        <form action="{{url('adm-maps/'.$option->id)}}" method="POST" enctype="multipart/form-data" class="forms-sample">
            @method('put')
            @csrf
            <input type="hidden" name="proses" value="send">
                <div class="form-group">
                    <label>LATITUDE</label>
                    <input class="form-control" type="text" name="latitude" readonly="readonly" id="lat" value="<?php echo $option['latitude'];?>">
                </div>          
                <div class="form-group">
                    <label>LONGITUDE</label>
                    <input class="form-control" type="text" name="longitude" readonly="readonly" id="lng" value="<?php echo $option['longitude'];?>">
                </div>
                <div class="form-group">
                    <label>ID PLACE</label>
                    <input class="form-control" type="text" name="idplace" readonly="readonly" id="idplace" value="<?php echo $option['idplace'];?>">
                </div>          
                        
                <div class="box-footer">
                    <button type="button" id="simpan" class="btn btn-primary" onclick="submit()">Submit</button>
                </div>
          </form>
        </div>
        </div>

        </div>
      </div>

      
      <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initMap"
      type="text/javascript" async defer></script>
      {{-- <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDgPniMS1gblKXTk5CnpfRDY3uRlM5HyBc=places"></script>  --}}
        <script type="text/javascript">
        function initMap(){
            var posisi = new google.maps.LatLng({{$option['latitude']}},{{$option['longitude']}});
            var peta;
            var opsi = {
                center : posisi,
                zoom:18,
                mapTypeId:google.maps.MapTypeId.ROADMAP
            };
            
            peta = new google.maps.Map(document.getElementById('map_canvas'),opsi);
            var markerawal = new google.maps.Marker({
                position:posisi
            });            
            //markerawal.setMap(peta);
            // google.maps.event.addListener(peta,'click',function(event){ 
            //     placeMarker(event.latLng);
            //     infowindow.open(peta,markerawal);
            // }); 

            var input = document.getElementById('pac-input');
            var autocomplete = new google.maps.places.Autocomplete(input);
                    autocomplete.bindTo('bounds', peta);
            peta.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            var marker = new google.maps.Marker({
                      map: peta,
                      position:posisi
                    });
            autocomplete.addListener('place_changed', function() {
                      var place = autocomplete.getPlace();

                      if (place.geometry.viewport) {
                        peta.fitBounds(place.geometry.viewport);
                      } else {
                        peta.setCenter(place.geometry.location);
                        peta.setZoom(17);
                      }

                      // Set the position of the marker using the place ID and location.
                      marker.setPlace({
                        placeId: place.place_id,
                        location: place.geometry.location
                      });
                      marker.setVisible(true);

                      $('#idplace').val(place.place_id);
                      $('#lat').val(place.geometry.location.lat());
                      $('#lng').val(place.geometry.location.lng());
                    });
            }

            // function placeMarker(location){
            //     var marker = new google.maps.Marker({
            //         position:location,
            //         map:peta
            //     });
                
                
            //     $('#lat').val(location.lat());
            //     $('#lng').val(location.lng());
            //     $('#idplace').val(location.idplace());

            // }

            google.maps.event.addDomListener(window,'load',initMap);
            </script>
@endsection