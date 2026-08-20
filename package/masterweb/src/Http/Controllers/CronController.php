<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\DemoEmail;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Feedback;
use \Smt\Masterweb\Models\Offer;
use Smt\Masterweb\Models\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Mail;

class CronController extends Controller
{
    public function email()
    {
        //looping users
        //get users
        $users = User::where('publish','1')->get();
        foreach ($users as $user) {
            $objDemo = new \stdClass();
            $objDemo->receiver = $user->name;
            $objDemo->devices = $user->devices;
            $demo = $user->devices;
            
            //sudah memiliki device
            if(count($user->devices) != 0)
            {
                Mail::to($user->email)->send(new DemoEmail($objDemo));
            }
            // return view('mails.demo',compact('demo'));
        }
    }
    
}
