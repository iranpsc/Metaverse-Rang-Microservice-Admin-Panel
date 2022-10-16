@extends('layouts.app')

@section('content')
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a href="#tab1" class="nav-link active" data-bs-toggle="tab">بسته ها</a>
        </li>
        <li class="nav-item">
            <a href="#tab2" class="nav-link" data-bs-toggle="tab">تاریخچه تغییرات بسته ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">ارزها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab4">تاریخچه تغییرات قیمت ارزها</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            @livewire('variables.color-options', ['options' => $options, 'variables' => $variables])
        </div>
        <div id="tab2" class="tab-pane fade active show">
            @livewire('variables.packages-change-logs', ['options' => $options])
        </div>
        <div id="tab3" class="tab-pane fade">
            @livewire('variables.colors-price', ['variables' => $variables])
        </div>
        <div id="tab4" class="tab-pane fade">
            @livewire('variables.variables-change-logs', ['variables' => $variables])
        </div>
    </div>
@endsection



