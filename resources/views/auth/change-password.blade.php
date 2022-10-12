<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <title>قالب مدیریتی مدیران</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="قالب مدیریت ایرانی مدیران ">
        <meta name="keywords" content="قالب مدیریت, قالب داشبورد, قالب ادمین, قالب مدیران, قالب مدیریت راستچین , قالب ایرانی مدیریت, پوسته مدیریت, قالب ادمین داشبورد سایت, قالب مدیریتی, مدیران, قالب مدیریت مدیران, پنل مدیریت, پنل مدیریت مدرن, قالب ادمین متریال, قالب مدیریت بوت استرپ, قالب ادمین بوتسترپ, قالب ادمین سایت, پوسته مدیریتی ایرانی, قالب مدیرتی مدیران ایرانی, بهترین قالب مدیریت, قالب مدیریت ریسپانسیو, قالب مدیریت ارزان, قالب admin">
        <link rel="shortcut icon" href="assets/images/favicon.png">

        <!-- BEGIN CSS -->
        <link href="{{ asset('assets/plugins/bootstrap/bootstrap5/css/bootstrap.rtl.min.css')}}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/simple-line-icons/css/simple-line-icons.min.css')}}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/paper-ripple/dist/paper-ripple.min.css')}}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/iCheck/skins/square/_all.css')}}" rel="stylesheet">
        <link href="{{ asset('assets/css/style.css')}}" rel="stylesheet">
        <link href="{{ asset('assets/css/colors.css')}}" rel="stylesheet">
        <!-- END CSS -->


    </head>
    <body class="fix-header active-ripple theme-blue dark">
        <!-- BEGIN LOEADING -->
        <div id="loader">
            <div class="spinner"></div>
        </div><!-- /loader -->
        <!-- END LOEADING -->

        <!-- BEGIN WRAPPER -->
        <div class="fixed-modal-bg"></div>
        <a href="#" class="btn btn-info btn-icon btn-round btn-lg" id="toggle-dark-mode">
            <i class="icon-bulb"></i>
        </a>
        <div class="modal-page shadow">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-md-12">
                        <p class="text-center m-t-30 m-b-40">
                            <i class="icon-lock-open border img-circle font-xxxlg p-20"></i>
                        </p>
                        <h2 class="text-center">بازیابی رمز عبور</h2>
                        <hr>

                        @if ($errors->any())
                            <ul>
                                @foreach ($errors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <form action="{{ route('password.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="sr-only control-label" for="email">رایانامه</label>
                                    <div class="input-group round">
                                        <span class="input-group-addon">
                                            <i class="icon-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control round ltr text-left" id="email" name="email" placeholder="مثال:info@site.com">
                                        @error('email')
                                            {{ $message }}
                                        @enderror
                                    </div><!-- /.input-group-->
                                </div><!-- /.form-group -->
                                <div class="form-group">
                                    <label for="password">رمز عبور جدید</label>
                                    <div class="input-group round">
                                        <span class="input-group-addon">
                                            <i class="icon-key"></i>
                                        </span>
                                        <input type="password" id="password" name="password" minlength="5" class="form-control ltr text-left">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">رمز عبور</label>
                                    <div class="input-group round">
                                        <span class="input-group-addon">
                                            <i class="icon-key"></i>
                                        </span>
                                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="5" class="form-control ltr text-left">
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-info btn-block btn-round">
                                    <i class="icon-check"></i>
                                    ذخیره
                                </button>
                            </div>
                        </form>
                    </div><!-- /.col-md-12 -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div><!-- /.modal-page -->
        <!-- END WRAPPER -->

        <!-- BEGIN JS -->
        <script src="{{ asset('assets/plugins/jquery/dist/jquery-3.1.0.js')}}"></script>
        <script src="{{ asset('assets/plugins/jquery-migrate/jquery-migrate-1.2.1.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/bootstrap/bootstrap5/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/paper-ripple/dist/PaperRipple.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/sweetalert2//dist/sweetalert2.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/iCheck/icheck.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/iCheck/icheck.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/jquery-validation/dist/jquery.validate.min.js')}}"></script>
        <script src="{{ asset('assets/plugins/jquery-validation/src/localization/messages_fa.js')}}"></script>
        <script src="{{ asset('assets/js/core.js')}}"></script>

        <!-- BEGIN PAGE JAVASCRIPT -->
        <script src="assets/js/pages/forget.js"></script>
        <!-- END PAGE JAVASCRIPT -->

    </body>
</html>












