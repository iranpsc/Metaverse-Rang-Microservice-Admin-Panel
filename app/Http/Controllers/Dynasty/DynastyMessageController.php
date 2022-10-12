<?php

namespace App\Http\Controllers\Dynasty;

use App\Http\Controllers\Controller;
use App\Models\Dynasty\DynastyMessage;
use Illuminate\Http\Request;

class DynastyMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 'Here Is Dynasty Messages  Management Section';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dynasty\DynastyMessage  $dynastyMessage
     * @return \Illuminate\Http\Response
     */
    public function show(DynastyMessage $dynastyMessage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dynasty\DynastyMessage  $dynastyMessage
     * @return \Illuminate\Http\Response
     */
    public function edit(DynastyMessage $dynastyMessage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dynasty\DynastyMessage  $dynastyMessage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DynastyMessage $dynastyMessage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dynasty\DynastyMessage  $dynastyMessage
     * @return \Illuminate\Http\Response
     */
    public function destroy(DynastyMessage $dynastyMessage)
    {
        //
    }
}
