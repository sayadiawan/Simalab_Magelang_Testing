<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

class MobileMenuController extends Controller
{
    /**
     * Display the mobile menu selection page
     */
    public function index()
    {
        return view('masterweb::mobile.menu');
    }
}

