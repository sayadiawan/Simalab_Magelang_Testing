<?php

namespace Smt\Masterweb\Http\Controllers\Api;

use Illuminate\Http\Request;
use Smt\Masterweb\Models\UserTele;
use Smt\Masterweb\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController\getRole;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;


class UserTeleController extends Controller
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
    

    public function index()
    {
        $usertele= UserTele::with('getUser')->select('id','username','tele_id', 'user_id', 'isLogin')->get();
        return response()->json([
            "user_tele"=>$usertele,
            "status"=>"Sukses"
        ],200);
    }

    public function store(Request $request)
    {
        $validator =  Validator::make($request->all(),[
            'username' => 'required',
            'tele_id' => 'required',
            ]);
        if($validator->fails()){
            return response()->json([
                "error" => 'validation_error',
                "message" => $validator->errors(),
            ], 401);
        }
       
        try{

            $usertele= new UserTele;
            $usertele->id= Uuid::uuid4();
            $usertele->username=$request->username;
            $usertele->tele_id=$request->tele_id;
            $usertele->isLogin=0;
            $usertele->save();
            return response()->json([
                "user_tele"=>$usertele,
                "status"=>"registered successfully'"
            ],200);
        }
        catch(Exception $e){
            return response()->json([
                "error" => "could_not_register",
                "message" => "Unable to register user"
            ], 400);
        }
    }

    public function loginTele(Request $request,$id)
    {
        $validator =  Validator::make($request->all(),[
            'username' => 'required',
            'password' => 'required',
            ]);
        if($validator->fails()){
            return response()->json([
                "error" => 'validation_error',
                "message" => $validator->errors(),
            ], 401);
        }

    
       
        try{

            $user = User::where( 'username', $request->username)->first();
            if($user!=null){
                if(password_verify($request->password, $user->password)){
                    $usertele= UserTele::where('tele_id',$id)->first();
                    $usertele->user_id= $user->id;
                    $usertele->isLogin=1;
                    $usertele->save();
                    return response()->json([
                        "user_tele"=>$usertele,
                        "user_login"=>$user,
                        "status"=>"registered successfully'"
                    ],200);
                }else{
                    return response()->json([
                        "error" => "could_not_register",
                        "message" => "Unable to register user"
                    ], 400);      
                }
    
            }else{
                return response()->json([
                    "error" => "could_not_register",
                    "message" => "Unable to register user"
                ], 400);    
            }
        }catch(Exception $e){
            return response()->json([
                "error" => "could_not_register",
                "message" => "Unable to register user"
            ], 400);
        }
    }

    
}