<?php

namespace App\Http\Controllers;

use App\Models\Level\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('level', ['levels' => Level::all()]);
    }
}
