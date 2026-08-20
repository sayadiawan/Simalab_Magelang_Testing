<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{

    public function index()
    {
        return view('masterweb::publik.beranda');
    }
}
