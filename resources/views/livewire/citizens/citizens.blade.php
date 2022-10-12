@extends('layouts.app')

@section('content')
    <div>
        <ul class="nav nav-tabs border">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab1">مشخصات ثبت نام</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab2">احراز هویت</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab3">حسابهای بانکی</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab4">واریزی ها</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab5">برداشت ها</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab6">جزئیات پروفایل</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab7">دارایی ها</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="tab1" class="tab-pane fade show active">
                @livewire('citizens.registration-info')
            </div>
            <div id="tab2" class="tab-pane fade">
                @livewire('citizens.kyc')
            </div>
            <div id="tab3" class="tab-pane fade">
                @livewire('citizens.bankaccounts')
            </div>
            <div id="tab4" class="tab-pane fade">
                @livewire('citizens.deposits')
            </div>
            <div id="tab5" class="tab-pane fade">
                @livewire('citizens.withdraws')
            </div>
            <div id="tab6" class="tab-pane fade">
                @livewire('citizens.profiledetails')
            </div>
            <div id="tab7" class="tab-pane fade">
                @livewire('citizens.assets')
            </div>
        </div>
    </div>
@endsection
