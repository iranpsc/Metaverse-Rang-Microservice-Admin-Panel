@extends('layouts.app')

@section('content')
    <ul class="nav nav-tabs border">
        <li class="nav-item">
            <a href="#tab1" class="nav-link active" data-bs-toggle="tab">بسته رنگ ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab2">قیمت psc</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab3">قیمت رنگ ها</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab4">رضایت</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab1" class="tab-pane fade active show">
            @livewire('variables.color-options')
        </div>
        <div id="tab2" class="tab-pane fade">
            @livewire('variables.psc-price')
        </div>
        <div id="tab3" class="tab-pane fade">
            @livewire('variables.colors-price')
        </div>
        <div id="tab4" class="tab-pane fade">
            رضایت
        </div>
    </div>
@endsection

@push('js')
    <script src="https://www.google.com/recaptcha/api.js?render=6LchpFAhAAAAAAC4XxT7zmsfzhwheUnV0Q0Mr2ue"></script>
    <script>
        function onClick(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('6LchpFAhAAAAAAC4XxT7zmsfzhwheUnV0Q0Mr2ue', {
                    action: 'submit'
                }).then(function(token) {
                    // Add your logic to submit to your backend server here.
                });
            });
        }

        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
    </script>
@endpush


