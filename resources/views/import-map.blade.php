@extends('layouts.app')

@section('content')
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissable fade show">
            <span class="close" data-bs-dismiss="alert">&times;</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="table-responsive">
        @if (count($maps) > 0)
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>نام نقشه</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($maps as $map)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $map['name'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <a href="{{ route('empty-and-reset-database') }}" class="btn btn-danger w-25 round my-2">ریست دیتابیس نقشه ها</a>
            <a href="{{ route('import-maps') }}" class="btn btn-primary w-25 round my-2">وارد کردن نقشه ها به دیتابیس</a>
        @else
            <x-alert :type="'danger'" :message="'نقشه ای یافت نشد'"></x-alert>
        @endif

    </div>
@endsection
