@extends('layouts.app')

@section('content')
    @if (session('status'))
        <div class="alert alert-success w-75 mx-auto" role="alert">
            {{ session('status') }}
        </div>
    @endif
    <div class="row">
        <div class="col-lg-4 offset-lg-4 col-md-8 offset-md-2 border shadow p-b-30">
            <p class="text-center m-t-40 m-b-40">
                <i class="icon-lock border border-primary img-circle text-primary font-xxxlg p-20"></i>
            </p>
            <hr>

            @if ($errors->any())
                <ul class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form action="{{ route('password.change.submit') }}" id="form" role="form" method="POST">
                @csrf
                <div class="form-body">
                    <div class="form-group row">
                        <label for="password" class="form-col-label col-sm-4">رمز عبور جدید</label>
                        <div class="col-sm-8">
                            <div class="input-group round">
                                <span class="input-group-addon">
                                    <i class="icon-key"></i>
                                </span>
                                <input type="password" id="password" name="password" minlength="5"
                                    class="form-control ltr text-left" required>
                            </div><!-- /.input-group -->
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group row">
                        <label for="confirm_password" class="form-col-label col-sm-4">تکرار رمز عبور جدید</label>
                        <div class="col-sm-8">
                            <div class="input-group round">
                                <span class="input-group-addon">
                                    <i class="icon-key"></i>
                                </span>
                                <input type="password" id="confirm_password" name="password_confirmation" minlength="5"
                                    class="form-control ltr text-left" required>
                            </div><!-- /.input-group -->
                        </div>
                    </div><!-- /.form-group -->
                </div><!-- /.form-body -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-info btn-round w-25">
                        <i class="icon-check"></i>
                        ذخیره
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-warning btn-round pull-left w-25">
                        <i class="icon-close"></i>
                        بازگشت
                    </a>
                </div><!-- /.form-actions -->
            </form>
        </div><!-- /.col -->
    </div><!-- /.row -->
@endsection
