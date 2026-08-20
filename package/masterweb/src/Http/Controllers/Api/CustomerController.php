<?php

namespace Smt\Masterweb\Http\Controllers\Api;

use Illuminate\Http\Request;
use Smt\Masterweb\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController\getRole;
use Ramsey\Uuid\Uuid;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\Analys;


class CustomerController extends Controller
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
        $page = $request->input('page');
        $resultCount = 10;

        $offset = ($page - 1) * $resultCount;

        $customers =  Customer::when($request->term, function ($query) use ($request) {
            $query->where('name_customer', 'like', "%{$request->term}%")
                ->orWhere('address_customer', 'like', "%{$request->term}%");
        })
        ->skip($offset)
        ->take($resultCount)
        // ->get(['id',Method::raw('params_method as text')]);
        ->select(array('id_customer AS id','name_customer AS text'))->get();

        $count = Customer::count();
        $endCount = $offset + $resultCount;

        $morePages = $endCount < $count;

        $results = array(
            "results" => $customers,
            "pagination" => array(
              "more" => $morePages
            )
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

    public function detail($id)
    {
        $customer =  Customer::where('id_customer',$id)->firstOrFail();
        
        return response()->json($customer,200);
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