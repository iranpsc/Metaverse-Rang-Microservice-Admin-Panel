<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CitizensController extends Controller
{
    public function index() {
        return view('citizens');
    }
}
