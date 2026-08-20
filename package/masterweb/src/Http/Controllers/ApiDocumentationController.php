<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    /**
     * Display API documentation page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('masterweb::module.public.api-documentation.index');
    }
}

