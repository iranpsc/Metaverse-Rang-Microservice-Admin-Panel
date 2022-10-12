@extends('layouts.app')

@section('content')
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">رضایت</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">قیمت psc</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">قیمت رنگ ها</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            @livewire('shop.satisfaction')
        </div>
        <div id="tab2" class="tab-pane fade">
            @livewire('shop.psc-price')
        </div>
        <div id="tab3" class="tab-pane fade">
            @livewire('shop.colors-price')
        </div>
    </div>
@endsection

