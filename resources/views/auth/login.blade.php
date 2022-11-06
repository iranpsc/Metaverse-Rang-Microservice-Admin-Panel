<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <title>پنل مدیریت سامانه متارنگ</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="assets/images/logo-rgb.png">

        <!-- BEGIN CSS -->
        <link href="{{ asset('assets/plugins/bootstrap/bootstrap5/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/simple-line-icons/css/simple-line-icons.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/sweetalert2/dist/sweetalert2.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/paper-ripple/dist/paper-ripple.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/plugins/iCheck/skins/square/_all.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/colors.css') }}" rel="stylesheet">
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

                    <div class="col-md-12 round">
                        <div class="logo-con m-t-10 m-b-10">
                            <img src="{{ asset('assets/images/logo-rgb.png') }}" class="light-logo center-block img-responsive" style="width:15%; height:15%" alt="logo">
                        </div><!-- /.logo-con -->

                        <h2 class="text-center m-b-20">وارد شوید</h2>
                        <hr>
                        @if (session('error'))
                            <x-alerts.danger>{{ session('error')['message'] }}</x-alerts.danger>
                        @endif

                        <form id="form" class="m-t-30 m-b-30" action="{{ route('login') }}" method="POST" role="form">
                            @csrf
                            <div class="form-group">
                                <label for="email" class="sr-only">رایانامه</label>
                                <div class="input-group round">
                                    <span class="input-group-addon">
                                        <i class="icon-envelope"></i>
                                    </span>
                                    <input id="email" class="form-control ltr text-left @error('email') has-error @enderror" type="email" name="email">
                                    @error('email')
                                        {{ $message }}
                                    @enderror
                                </div><!-- /.input-group -->
                            </div><!-- /.form-group -->
                            <div class="form-group">
                                <label for="password" class="sr-only">رمز عبور</label>
                                <div class="input-group round">
                                    <span class="input-group-addon">
                                        <i class="icon-key"></i>
                                    </span>
                                    <input id="password" class="form-control ltr text-left @error('password') has-error @enderror" type="password" name="password">
                                     @error('password')
                                        {{ $message }}
                                    @enderror
                                </div><!-- /.input-group -->
                            </div><!-- /.form-group -->
                            <p>
                                <button class="btn btn-info btn-round btn-block" type="submit">
                                    <i class="icon-paper-plane font-lg"></i>
                                    ورود
                                </button>
                            </p>
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
        <script src="{{ asset('assets/js/pages/login.js')}}"></script>
        <!-- END PAGE JAVASCRIPT -->

    </body>
</html>
