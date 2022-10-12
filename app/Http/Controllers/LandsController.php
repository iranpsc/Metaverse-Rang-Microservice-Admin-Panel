<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Feature\FeaturePricingLimit;
use Illuminate\Http\Request;

class LandsController extends Controller
{
    public function index() {
        return view('lands');
    }
}
