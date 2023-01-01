@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('تایید آدرس ایمیل') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('لینک تایید حساب کاربری برایتان ارسال گردید.') }}
                        </div>
                    @endif

                    {{ __('لطفا قبل از رفتن به مرحله بعد، آدرس ایمیل خود را بررسی نمایید.') }}
                    {{ __('اگر ایمیل برایتان ارسال نگردیده است') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('برای ارسال مجدد کلیک کنید') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
