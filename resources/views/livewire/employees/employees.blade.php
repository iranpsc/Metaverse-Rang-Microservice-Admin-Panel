@extends('layouts.app')

@section('content')
<div>
    {{-- Nothing in the world is as soft and yielding as water. --}}

    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab1">مشخصات حقیقی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">بانک</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">اسناد</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab4">حقوق و دستمزد</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab5">کارت زمان</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab6">وظایف محوله</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            @livewire('employees.listing')
        </div>
        <div id="tab2" class="tab-pane fade">
            @livewire('employees.bank')
        </div>
        <div id="tab3" class="tab-pane fade">
            @livewire('employees.documents')
        </div>
        <div id="tab4" class="tab-pane fade">
            @livewire('employees.salary')
        </div>
        <div id="tab5" class="tab-pane fade">
            @livewire('employees.time')
        </div>
        <div id="tab6" class="tab-pane fade">
            @livewire('employees.tasks')
        </div>
    </div>
@endsection
