<?php

namespace Smt\Masterweb\Http\Controllers\Api;

use Illuminate\Http\Request;
use Smt\Masterweb\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController\getRole;
use Ramsey\Uuid\Uuid;
use \Smt\Masterweb\Models\Method;
use \Smt\Masterweb\Models\Packet;
use \Smt\Masterweb\Models\PacketDetail;
use \Smt\Masterweb\Models\Analys;


class PacketController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
  
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // dd($request->all());
        $page = $request->input('page');
        $resultCount = 10;

        $offset = ($page - 1) * $resultCount;

        $methods =  Packet::join('ms_sample_type', function($join) {
            $join->on('ms_sample_type.id_sample_type', '=', 'ms_packet.sample_type_id')
                ->whereNull('ms_sample_type.deleted_at')
                ->whereNull('ms_packet.deleted_at');
        })->when($request->term, function ($query) use ($request) {
            $query->where('ms_packet.name_packet', 'like', "%{$request->term}%")
                ->orWhere('ms_sample_type.name_sample_type', 'like', "%{$request->term}%");
        })
        ->skip($offset)
        ->take($resultCount)
        ->select(array('ms_packet.id_packet AS id','ms_packet.name_packet AS text','ms_packet.price_total_packet AS data-extra'))->get();

        $count = Method::count();
        $endCount = $offset + $resultCount;

        $morePages = $endCount < $count;

        $results = array(
            "results" => $methods,
            "pagination" => array(
              "more" => $morePages)
          );

        
        // $methods= Method::when($request->term, function ($query) use ($request) {
        //     $query->where('params_method', 'like', "%{$request->term}%")
        //         ->orWhere('name_method', 'like', "%{$request->term}%");
        // })->select(array('id_method AS _id','params_method AS name'))->get();
        

        return response()->json($results,200);
    }


    public function sampletype($sample_type,$id_jenis_makanan=null){


        $methods =  Packet::where('ms_sample_type.id_sample_type',$sample_type)
                        ->where('ms_packet.jenis_makanan_id',$id_jenis_makanan)
                        ->join('ms_sample_type', function($join) {
                            $join->on('ms_sample_type.id_sample_type', '=', 'ms_packet.sample_type_id')
                                ->whereNull('ms_sample_type.deleted_at')
                                ->whereNull('ms_packet.deleted_at');
                        })
                        ->select(array('ms_packet.id_packet AS id','ms_packet.name_packet AS text','ms_packet.price_total_packet AS data_extra'))->get();
                            
        $results = array(
            "results" => $methods
          );

        
        // $methods= Method::when($request->term, function ($query) use ($request) {
        //     $query->where('params_method', 'like', "%{$request->term}%")
        //         ->orWhere('name_method', 'like', "%{$request->term}%");
        // })->select(array('id_method AS _id','params_method AS name'))->get();
        

        return response()->json($results,200);

    }
    

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}