@extends('layouts.app')

@section('content')
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">پشتیبانی فنی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">امینت شهروندان</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">سرمایه گذاری</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab4">بازرسی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab5">حراست</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab6">مدیریت کل ز.ت.ب</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="tab1" class="tab-pane fade show active">
            @livewire('support.technical-support', ['tickets' => $tickets])
        </div>
        <div id="tab2" class="tab-pane fade">
            @livewire('support.citizens-safety', ['tickets' => $tickets])
        </div>
        <div id="tab3" class="tab-pane fade">
            @livewire('support.investment', ['tickets' => $tickets])
        </div>
        <div id="tab4" class="tab-pane fade">
            @livewire('support.inspection', ['tickets' => $tickets])
        </div>
        <div id="tab5" class="tab-pane fade">
            @livewire('support.protection', ['tickets' => $tickets])
        </div>
        <div id="tab6" class="tab-pane fade">
            @livewire('support.z-t-b', ['tickets' => $tickets])
        </div>
    </div>
@endsection
