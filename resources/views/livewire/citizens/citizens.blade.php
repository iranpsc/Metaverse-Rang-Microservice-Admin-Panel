@extends('layouts.app')

@section('content')
    <div>
        <ul class="nav nav-tabs border">
            @can('Registration-Info')
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab1">مشخصات ثبت نام</a>
                </li>
            @endcan
            @can('Authorize')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab2">احراز هویت</a>
                </li>
            @endcan
            @can('Bank-Account')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab3">حسابهای بانکی</a>
                </li>
            @endcan
            @can('Deposit')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab4">واریزی ها</a>
                </li>
            @endcan
            @can('Withdraw')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab5">برداشت ها</a>
                </li>
            @endcan
            @can('Profile-Info')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab6">جزئیات پروفایل</a>
                </li>
            @endcan
            @can('User-Assets')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab7">دارایی ها</a>
                </li>
            @endcan
        </ul>
        <div class="tab-content">
            @can('Registration-Info')
                <div id="tab1" class="tab-pane fade show active">
                    @livewire('citizens.registration-info')
                </div>
            @endcan
            @can('Authorize')
                <div id="tab2" class="tab-pane fade">
                    @livewire('citizens.kyc')
                </div>
            @endcan
            @can('Bank-Account')
                <div id="tab3" class="tab-pane fade">
                    @livewire('citizens.bankaccounts')
                </div>
            @endcan
            @can('Deposit')
                <div id="tab4" class="tab-pane fade">
                    @livewire('citizens.deposits')
                </div>
            @endcan
            @can('Withdraw')
                <div id="tab5" class="tab-pane fade">
                    @livewire('citizens.withdraws')
                </div>
            @endcan
            @can('Profile-Info')
                <div id="tab6" class="tab-pane fade">
                    @livewire('citizens.profiledetails')
                </div>
            @endcan
            @can('User-Assets')
                <div id="tab7" class="tab-pane fade">
                    @livewire('citizens.assets')
                </div>
            @endcan
        </div>
    </div>
@endsection
