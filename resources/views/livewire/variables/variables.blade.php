@extends('layouts.app')

@section('content')
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a href="#tab1" class="nav-link active" data-bs-toggle="tab">بسته ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">ارزها</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            @livewire('variables.color-options')
        </div>
        <div id="tab2" class="tab-pane fade">
            @livewire('variables.colors-price')
        </div>
    </div>
@endsection



