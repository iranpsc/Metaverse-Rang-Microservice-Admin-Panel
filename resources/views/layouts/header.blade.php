<!DOCTYPE html>
<html lang="fa" dir="rtl" class="rtl">

<head>
    <title>پنل مدیریت سامانه متارنگ</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="fontiran.com:license" content="NE29X">
    <link rel="shortcut icon" href="assets/images/logo-rgb.png">


    <script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>
    <!-- BEGIN CSS -->
    <link href="{{ asset('assets/plugins/bootstrap/bootstrap5/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/metisMenu/dist/metisMenu.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/simple-line-icons/css/simple-line-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('assets/plugins/switchery/dist/switchery.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/paper-ripple/dist/paper-ripple.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/iCheck/skins/square/_all.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/colors.css') }}" rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/data-table/DataTables-1.10.16/css/jquery.dataTables.css') }}">
    <link href="{{ asset('assets/plugins/datepicker/jquery.ui.datepicker1.8-all.css') }}" rel="stylesheet">
    <!-- END CSS -->

    <!-- BEGIN PAGE CSS -->
    <link href="{{ asset('assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.css') }}" rel="stylesheet">
    <!-- END PAGE CSS -->

    @livewireStyles

    @stack('css')

</head>

<body class="active-ripple theme-blue fix-header sidebar-extra dark">
    <!-- BEGIN LOEADING -->
    <div id="loader">
        <div class="spinner"></div>
    </div><!-- /loader -->
    <!-- END LOEADING -->

    <!-- BEGIN HEADER -->
    <div class="navbar navbar-fixed-top" id="main-navbar">
        <div class="header-right">
            <a href="dashboard.html" class="logo-con">
                <img src="assets/images/logo.png" class="img-responsive center-block" alt="لوگو قالب مدیران">
            </a>
        </div><!-- /.header-right -->
        <div class="header-left">
            <div class="top-bar">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="#" class="btn" id="toggle-sidebar">
                            <span class="menu"></span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="btn open" id="toggle-sidebar-top">
                            <i class="icon-user-following"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="btn" id="toggle-dark-mode">
                            <i class="icon-bulb"></i>
                        </a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-left">
                    <li class="dropdown">
                        <a href="#" class="btn" id="toggle-fullscreen">
                            <i class="icon-size-fullscreen"></i>
                        </a>
                    </li>
                    <li class="dropdown dropdown-messages">
                        <a href="#messages" class="btn">
                            <i class="icon-envelope"></i>
                            <span class="badge badge-primary">
                                {{ count(Auth::user()->unreadNotifications) }}
                            </span>
                        </a>
                    </li>
                    <li class="dropdown dropdown-announces">
                        <a href="#" class="dropdown-toggle btn" data-bs-toggle="dropdown">
                            <i class="icon-bell"></i>
                            <span class="badge badge-warning">
                                5
                            </span>
                        </a>
                        <ul class="dropdown-menu has-scrollbar">
                            <li class="dropdown-header clearfix">
                                <span class="float-start">
                                    <a href="#" rel="tooltip" title="خواندن همه" data-placement="left">
                                        <i class="icon-eye"></i>
                                    </a>
                                    <span>
                                        شما 8 اعلان تازه دارید.
                                    </span>
                                </span>

                            </li>
                            <li class="dropdown-body">
                                <ul class="dropdown-menu-list">
                                    <li class="clearfix">
                                        <a href="#">
                                            <p class="clearfix">
                                                <strong class="float-start">عباس دوران</strong>
                                                <small class="float-end text-muted">
                                                    <i class="icon-clock"></i>
                                                    21:30
                                                </small>
                                            </p>
                                            <p>بسته ارسالی شما به دستم رسید.</p>
                                        </a>
                                    </li>
                                    <li class="clearfix">
                                        <a href="#">
                                            <p class="clearfix">
                                                <strong class="float-start">حسن باقری</strong>
                                                <small class="float-end text-muted">
                                                    <i class="icon-clock"></i>
                                                    20:20
                                                </small>
                                            </p>
                                            <p>از محبت شما ممنونم.</p>
                                        </a>
                                    </li>
                                    <li class="clearfix">
                                        <a href="#">
                                            <p class="clearfix">
                                                <strong class="float-start">مدیر کل</strong>
                                                <small class="float-end text-muted">
                                                    <i class="icon-clock"></i>
                                                    19:20
                                                </small>
                                            </p>
                                            <p>سفارش شما ارسال گردید..</p>
                                        </a>
                                    </li>
                                    <li class="clearfix">
                                        <a href="#">
                                            <p class="clearfix">
                                                <strong class="float-start">مدیر مالی</strong>
                                                <small class="float-end text-muted">
                                                    <i class="icon-clock"></i>
                                                    17:40
                                                </small>
                                            </p>
                                            <p>درخواست فیش حقوقی</p>
                                        </a>
                                    </li>
                                    <li class="clearfix">
                                        <a href="#">
                                            <p class="clearfix">
                                                <strong class="float-start">ابراهیم همت</strong>
                                                <small class="float-end text-muted">
                                                    <i class="icon-clock"></i>
                                                    15:45
                                                </small>
                                            </p>
                                            <p>پیام های مرا دنبال کنید.</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown-footer clearfix">
                                <a href="#">
                                    <i class="icon-list fa-flip-horizontal"></i>
                                    مشاهده همه اعلانات
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="dropdown dropdown-user">
                        <a href="#" class="dropdown-toggle dropdown-hover" data-bs-toggle="dropdown">
                            <img src="assets/images/user/48.png" alt="عکس پرفایل" class="img-circle img-responsive">
                            <span>{{ Auth::user()->name }}</span>
                            <i class="icon-arrow-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="edit_profile.html">
                                    <i class="icon-note"></i>
                                    ویرایش پروفایل
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('password.change') }}">
                                    <i class="icon-key"></i>
                                    تغییر رمز عبور
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="chat.html">
                                    <span class="badge badge-primary float-end">
                                        {{ count(Auth::user()->unreadNotifications) }} </span>
                                    <i class="icon-envelope"></i>
                                    تیکت های جدید
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('خروج') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul><!-- /.navbar-left -->
            </div><!-- /.top-bar -->
        </div><!-- /.header-left -->
    </div><!-- /.navbar -->
    <!-- END HEADER -->
