<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DynastyController extends Controller
{
    public function index()
    {
        return view('dynasty');
    }
}
