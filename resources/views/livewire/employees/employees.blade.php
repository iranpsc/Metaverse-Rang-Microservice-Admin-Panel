@extends('layouts.app')

@section('content')
    <div>
        {{-- Nothing in the world is as soft and yielding as water. --}}

        <ul class="nav nav-tabs border">
            @can('Employee-Actual-Info')
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab1">مشخصات حقیقی</a>
                </li>
            @endcan
            @can('Employee-Bank')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab2">بانک</a>
                </li>
            @endcan
            @can('Employee-Documents')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab3">اسناد</a>
                </li>
            @endcan
            @can('Employee-Salary')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab4">حقوق و دستمزد</a>
                </li>
            @endcan
            @can('Employee-Time-Card')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab5">کارت زمان</a>
                </li>
            @endcan
            @can('Employee-Duties')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab6">وظایف محوله</a>
                </li>
            @endcan
        </ul>

        <div class="tab-content">
            @can('Employee-Actual-Info')
                <div id="tab1" class="tab-pane fade active show">
                    @livewire('employees.listing')
                </div>
            @endcan
            @can('Employee-Bank')
                <div id="tab2" class="tab-pane fade">
                    @livewire('employees.bank')
                </div>
            @endcan
            @can('Employee-Documents')
                <div id="tab3" class="tab-pane fade">
                    @livewire('employees.documents')
                </div>
            @endcan
            @can('Employee-Salary')
                <div id="tab4" class="tab-pane fade">
                    @livewire('employees.salary')
                </div>
            @endcan
            @can('Employee-Time-Card')
                <div id="tab5" class="tab-pane fade">
                    @livewire('employees.time')
                </div>
            @endcan
            @can('Employee-Duties')
                <div id="tab6" class="tab-pane fade">
                    @livewire('employees.tasks')
                </div>
            @endcan
        </div>
@endsection
