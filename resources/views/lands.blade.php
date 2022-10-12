@extends('layouts.app')

@section('content')
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">کل زمین ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">قیمت زمین ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">ورود به زمین ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab4">زمین های فروخته شده</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab5">رنگ های فروخته شده</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab6">مبادله زمین</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab7">قیمت گذاری زمین</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab8">هزینه ورودی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab9">محدودیت قیمت گذاری</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            @livewire('lands.lands-listing')
        </div>
        <div id="tab2" class="tab-pane fade">
            @livewire('lands.lands-price')
        </div>
        <div id="tab3" class="tab-pane fade">
            @livewire('lands.lands-entries')
        </div>
        <div id="tab4" class="tab-pane fade">
            @livewire('lands.lands-sold')
        </div>
        <div id="tab5" class="tab-pane fade">
            @livewire('lands.sold-colors')
        </div>
        <div id="tab6" class="tab-pane fade">
            @livewire('lands.traded-lands')
        </div>
        <div id="tab7" class="tab-pane fade">
            @livewire('lands.pricing-lands')
        </div>
        <div id="tab8" class="tab-pane fade">
            @livewire('lands.entry-price')
        </div>

        <div id="tab9" class="tab-pane fade">
            @livewire('lands.feature-pricing-limits')
        </div>

    </div>
@endsection
