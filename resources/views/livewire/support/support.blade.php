@extends('layouts.app')

@section('content')
    <ul class="nav nav-tabs border">
        @can('Technical-Support')
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab1">پشتیبانی فنی</a>
            </li>
        @endcan
        @can('Citizen-Security-Support')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab2">امینت شهروندان</a>
            </li>
        @endcan
        @can('Investment-Support')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab3">سرمایه گذاری</a>
            </li>
        @endcan
        @can('Inspection-Support')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab4">بازرسی</a>
            </li>
        @endcan
        @can('Protection-Support')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab5">حراست</a>
            </li>
        @endcan
        @can('Co-Management-Support')
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab6">مدیریت کل ز.ت.ب</a>
            </li>
        @endcan
    </ul>

    <div class="tab-content">
        @can('Technical-Support')
            <div id="tab1" class="tab-pane fade show active">
                @livewire('support.technical-support', ['tickets' => $tickets])
            </div>
        @endcan
        @can('Citizen-Security-Support')
            <div id="tab2" class="tab-pane fade">
                @livewire('support.citizens-safety', ['tickets' => $tickets])
            </div>
        @endcan
        @can('Investment-Support')
            <div id="tab3" class="tab-pane fade">
                @livewire('support.investment', ['tickets' => $tickets])
            </div>
        @endcan
        @can('Inspection-Support')
            <div id="tab4" class="tab-pane fade">
                @livewire('support.inspection', ['tickets' => $tickets])
            </div>
        @endcan
        @can('Protection-Support')
            <div id="tab5" class="tab-pane fade">
                @livewire('support.protection', ['tickets' => $tickets])
            </div>
        @endcan
        @can('Co-Management-Support')
            <div id="tab6" class="tab-pane fade">
                @livewire('support.z-t-b', ['tickets' => $tickets])
            </div>
        @endcan
    </div>
@endsection
