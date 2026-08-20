<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Sample;
use \Smt\Masterweb\Models\SamplesMethod;


use \Smt\Masterweb\Models\MethodSampling;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Container;
use \Smt\Masterweb\Models\Packet;


use SimpleSoftwareIO\QrCode\Facades\QrCode;

use PDF;
use DB;

use Mapper;
use \Smt\Masterweb\Models\Pencahayaan;
use \Smt\Masterweb\Models\PencahayaanDetail;


use Carbon\Carbon;


class LaboratoriumPencahayaanManagement extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        //get auth user
        //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
        //get auth user
        // $user = Auth()->user();
        // $samples = Sample::orderBy('created_at')->get();

        

        // return view('masterweb::module.admin.laboratorium.sample.list',compact('user','samples'));


        $permohonan_uji = PermohonanUji::where("id_permohonan_uji" , $id)
                ->leftJoin('ms_customer', 'ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
                ->leftJoin('ms_industry', 'ms_customer.category_customer', '=', 'ms_industry.id_industry')
                ->first();
        
        
        $pencahayaan = Pencahayaan::where("permohonan_uji_id", $permohonan_uji->id_permohonan_uji)->first();

        if($pencahayaan!=null){

            $pencahayaan_detail = PencahayaanDetail::where("pencahayaan_id", $pencahayaan->id_pencahayaan)->get();


            Mapper::map($pencahayaan->lat_location_sampling,$pencahayaan->long_location_sampling,['draggable' => true, 
            'eventBeforeLoad' => '
                    google.maps.event.addDomListener(window, "load", new function() {
                    
                        
                        
                            
                        setTimeout(function () {


                            var peta;
                            var input = document.getElementById("pac-input");
                            var divinput = document.getElementById("pac-input-div");
                            var autocomplete = new google.maps.places.Autocomplete(input);
                            autocomplete.bindTo("bounds", map);
                            map.controls[google.maps.ControlPosition.TOP_CENTER].push(divinput);

                            document.getElementById("lat").value = marker_0.getPosition().lat();
                            document.getElementById("lng").value = marker_0.getPosition().lng();


                    
                            
                    
                            autocomplete.addListener("place_changed", function() {
                                var place = autocomplete.getPlace();
                                if (place.geometry.viewport) {
                                    map.fitBounds(place.geometry.viewport);
                                } else {
                                    map.setCenter(place.geometry.location);
                                }
                    
                                // Set the position of the marker using the place ID and location.
                                marker_0 .setPosition(place.geometry.location);
                                
                    
                            
                                $("#idplace").val(place.place_id);
                                $("#lat").val(place.geometry.location.lat());
                                $("#lng").val(place.geometry.location.lng());

                            
                            });

                            $("#lat").on("input", function() {
                                var lat = parseFloat($(this).val());
                                var long = parseFloat($("#lng").val());
                                marker_0 .setPosition(new google.maps.LatLng(lat, long));
                                map.setCenter(marker_0.getPosition());  
                            });

                            $("#lng").on("input", function() {
                                var lat = parseFloat($("#lat").val());
                                var long = parseFloat($(this).val());
                                marker_0 .setPosition(new google.maps.LatLng(lat, long));
                                map.setCenter(marker_0.getPosition());  
                            });
                    

                            
                        
                        }, 500);
                    });
                ','eventAfterLoad' => '
                       
                        $("#get_location").click(function(){
                            getLocation();
                        })
            

                        function getLocation() {
                            if (navigator.geolocation) {
                                // navigator.geolocation.watchPosition(showPosition);
                                navigator.geolocation.getCurrentPosition(showPosition);
                            } else {
                                Swal.fire({
                                title: "ERROR PROSES!",
                                text: "Geolocation is not supported by this browser.",
                                icon: "error",
                                timer: 5000,
                                button: false,
                                });
                            }
                        }

                        google.maps.event.addListener(maps[0].map, "click", function(event) {
                        
                            marker_0.setPosition(event.latLng);
                            document.getElementById("lat").value = event.latLng.lat();
                            document.getElementById("lng").value = event.latLng.lng();
                            map.setCenter(marker_0.getPosition()); 
                            console.log("Lat: "+event.latLng.lat()+" Long: "+event.latLng.lng())
                        });



                        function showError(error) {
                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                Swal.fire({
                                    title: "ERROR PROSES!",
                                    text: "User denied the request for Geolocation.",
                                    icon: "error",
                                    timer: 5000,
                                    button: false,
                                })
                                break;
                                case error.POSITION_UNAVAILABLE:
                                Swal.fire({
                                    title: "ERROR PROSES!",
                                    text: "Location information is unavailable.",
                                    icon: "error",
                                    timer: 5000,
                                    button: false,
                                })
                                break;
                                case error.TIMEOUT:
                                Swal.fire({
                                    title: "ERROR PROSES!",
                                    text: "The request to get user location timed out.",
                                    icon: "error",
                                    timer: 5000,
                                    button: false,
                                })
                                break;
                                case error.UNKNOWN_ERROR:
                                Swal.fire({
                                    title: "ERROR PROSES!",
                                    text: "An unknown error occurred.",
                                    icon: "error",
                                    timer: 5000,
                                    button: false,
                                })
                                break;
                            }
                        }

                        function showPosition(position) {
                            lat_now=position.coords.latitude;
                            long_now=position.coords.longitude;
                            marker_0.setPosition(new google.maps.LatLng(lat_now, long_now));
                            document.getElementById("lat").value = lat_now;
                            document.getElementById("lng").value = long_now;
                            console.log("Lat: "+lat_now+" Long: "+long_now)
                            map.setCenter(marker_0.getPosition());  
                        }

            ']);
        }else{
            $pencahayaan_detail = [];

            Mapper::location('Indonesia')->map(['draggable' => true, 
                'eventBeforeLoad' => '
                        google.maps.event.addDomListener(window, "load", new function() {
                        
                            
                            
                                
                            setTimeout(function () {


                                var peta;
                                var input = document.getElementById("pac-input");
                                var divinput = document.getElementById("pac-input-div");
                                var autocomplete = new google.maps.places.Autocomplete(input);
                                autocomplete.bindTo("bounds", map);
                                map.controls[google.maps.ControlPosition.TOP_CENTER].push(divinput);

                                document.getElementById("lat").value = marker_0.getPosition().lat();
                                document.getElementById("lng").value = marker_0.getPosition().lng();


                        
                                
                        
                                autocomplete.addListener("place_changed", function() {
                                    var place = autocomplete.getPlace();
                                    if (place.geometry.viewport) {
                                        map.fitBounds(place.geometry.viewport);
                                    } else {
                                        map.setCenter(place.geometry.location);
                                    }
                        
                                    // Set the position of the marker using the place ID and location.
                                    marker_0 .setPosition(place.geometry.location);
                                    
                        
                                
                                    $("#idplace").val(place.place_id);
                                    $("#lat").val(place.geometry.location.lat());
                                    $("#lng").val(place.geometry.location.lng());

                                
                                });

                                $("#lat").on("input", function() {
                                    var lat = parseFloat($(this).val());
                                    var long = parseFloat($("#lng").val());
                                    marker_0 .setPosition(new google.maps.LatLng(lat, long));
                                    map.setCenter(marker_0.getPosition());  
                                });

                                $("#lng").on("input", function() {
                                    var lat = parseFloat($("#lat").val());
                                    var long = parseFloat($(this).val());
                                    marker_0 .setPosition(new google.maps.LatLng(lat, long));
                                    map.setCenter(marker_0.getPosition());  
                                });
                        

                                
                            
                            }, 500);
                        });
                    ','eventAfterLoad' => '
                            getLocation();

                            $("#get_location").click(function(){
                                getLocation();
                            })
                

                            function getLocation() {
                                if (navigator.geolocation) {
                                    // navigator.geolocation.watchPosition(showPosition);
                                    navigator.geolocation.getCurrentPosition(showPosition);
                                } else {
                                    Swal.fire({
                                    title: "ERROR PROSES!",
                                    text: "Geolocation is not supported by this browser.",
                                    icon: "error",
                                    timer: 5000,
                                    button: false,
                                    });
                                }
                            }

                            google.maps.event.addListener(maps[0].map, "click", function(event) {
                            
                                marker_0.setPosition(event.latLng);
                                document.getElementById("lat").value = event.latLng.lat();
                                document.getElementById("lng").value = event.latLng.lng();
                                map.setCenter(marker_0.getPosition()); 
                                console.log("Lat: "+event.latLng.lat()+" Long: "+event.latLng.lng())
                            });



                            function showError(error) {
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                    Swal.fire({
                                        title: "ERROR PROSES!",
                                        text: "User denied the request for Geolocation.",
                                        icon: "error",
                                        timer: 5000,
                                        button: false,
                                    })
                                    break;
                                    case error.POSITION_UNAVAILABLE:
                                    Swal.fire({
                                        title: "ERROR PROSES!",
                                        text: "Location information is unavailable.",
                                        icon: "error",
                                        timer: 5000,
                                        button: false,
                                    })
                                    break;
                                    case error.TIMEOUT:
                                    Swal.fire({
                                        title: "ERROR PROSES!",
                                        text: "The request to get user location timed out.",
                                        icon: "error",
                                        timer: 5000,
                                        button: false,
                                    })
                                    break;
                                    case error.UNKNOWN_ERROR:
                                    Swal.fire({
                                        title: "ERROR PROSES!",
                                        text: "An unknown error occurred.",
                                        icon: "error",
                                        timer: 5000,
                                        button: false,
                                    })
                                    break;
                                }
                            }

                            function showPosition(position) {
                                lat_now=position.coords.latitude;
                                long_now=position.coords.longitude;
                                marker_0.setPosition(new google.maps.LatLng(lat_now, long_now));
                                document.getElementById("lat").value = lat_now;
                                document.getElementById("lng").value = long_now;
                                console.log("Lat: "+lat_now+" Long: "+long_now)
                                map.setCenter(marker_0.getPosition());  
                            }

                ']);
        }
        


        

        return view('masterweb::module.admin.laboratorium.pencahayaan.list',compact('permohonan_uji','pencahayaan','pencahayaan_detail')); 

                
    }


    public function printLHU($id){
       // dd("ygygyu");

       $sample = Sample::findOrFail($id);

       $customer = Customer::findOrFail($sample->customer_samples);
       $category = Industry::findOrFail($customer->category_customer);
       return view('masterweb::module.admin.laboratorium.permohonan-uji.printLHU',compact('sample','customer','category')); 

    
    }


    // public function save(Request $request){
    //     $validated = $request->validate([
    //         'cuaca' => ['required'],
    //         'arah_angin' => ['required'],
    //         'waktu_pengukuran' => ['required'],
    //         'hasil_pengujian' => ['required'],
    //         'pukul_pengukuran' => ['required'],
    //         'arah_angin' => ['required'],
            
            
            
    //     ]);
    // }

   


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
  


   


  

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $data =$request->all();
        
        // dd($data);
        $validated = $request->validate([
            'date_sampling' => ['required'],
            'lat' => ['required'],
            'lng' => ['required'],
            'cuaca' => ['required'],
            'arah_angin' => ['required'],
            'waktu_pengukuran' => ['required'],
            'pukul_pengukuran' => ['required'],
            'hasil_pengujian' => ['required'],
            'permohonan_uji_id' => ['required'],
            'data_table' => ['required'],
          
        ]);

        $pencahayaan_before = Pencahayaan::where('permohonan_uji_id',$data["permohonan_uji_id"])->first();

        if($pencahayaan_before!=null){
            $pencahayaan_before->delete();
            $pencahayaan_detail_before = PencahayaanDetail::where('pencahayaan_id',$pencahayaan_before->id_pencahayaan)->delete();
        }


        $user = Auth()->user();
        $pencahayaan = new Pencahayaan;
        //uuid
        $id_pencahayaan = Uuid::uuid4()->toString();

        $pencahayaan->id_pencahayaan  = $id_pencahayaan;
        $pencahayaan->permohonan_uji_id = $data["permohonan_uji_id"];

        $pencahayaan->tanggal_sampling =Carbon::createFromFormat('d/m/Y', $data["date_sampling"])->format('Y-m-d H:i:s');
        $pencahayaan->lat_location_sampling =$data["lat"];
        $pencahayaan->long_location_sampling =$data["lng"];
        $pencahayaan->cuaca_pencahayaan =$data["cuaca"];
        $pencahayaan->arah_angin =$data["arah_angin"];
        $pencahayaan->waktu_pengukuran =$data["waktu_pengukuran"];
        $pencahayaan->pukul_pengukuran =Carbon::createFromFormat('H:i', $data["pukul_pengukuran"])->format('H:i:s');
        $pencahayaan->hasil_pengujian =$data["hasil_pengujian"];

        $pencahayaan->save();

        for($i = 0; $i < count($data["data_table"]);$i++ ) {
            if(isset($data["data_table"][$i])){
                $pencahayaandetail = new PencahayaanDetail;
                $pencahayaandetail->id_pencahayaan_detail= Uuid::uuid4();
                $pencahayaandetail->pencahayaan_id=$pencahayaan->id_pencahayaan;
                $pencahayaandetail->hasil_pencahayaan_detail=$data["data_table"][$i];
                $pencahayaandetail->save();
            }
        }


       

 
        return response()->json(
            [
                'success'=>'Ajax request submitted successfully',
                'data'=>$data,
            ]);
         //return redirect()->route('user-client-management.index',[$request->get('client_id')])->with(['status'=>'User succesfully inserted','user'=>$user]);
  
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $auth = Auth()->user();

        $customer = Customer::where('id_customer',$id)->first();

        $categories= Industry::all();
       

        return view('masterweb::module.admin.laboratorium.permohonan-uji.edit',compact('customer','auth','categories','id'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {

        
        // print_r($data);
         
      
        
         $data =$request->all();
         $customer = Customer::find($id);
         $customer->name_customer = $request->post('name_customer');
         $customer->address_customer = $request->post('address_customer');
         $customer->email_customer = $request->post('email_customer');
         $customer->category_customer =$request->post('category_customer');
         $customer->cp_customer =$request->post('cp_customer');
         $customer->save();
 
         return redirect()->route('elits-permohonan-uji.index')->with(['status'=>'Customer succesfully updated']);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('elits-permohonan-uji.index')->with('status', 'Data berhasil dihapus');
    }
}
