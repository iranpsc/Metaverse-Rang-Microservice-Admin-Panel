@extends('layouts.app')

@section('content')
    @livewire('level.listing', ['levels' => $levels])
@endsection
