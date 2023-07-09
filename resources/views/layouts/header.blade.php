<!DOCTYPE html>
<html lang="fa" dir="rtl" class="rtl">

<head>
    <title>پنل مدیریت سامانه متارنگ</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="fontiran.com:license" content="NE29X">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo-rgb.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- END CSS -->

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
            <a href="{{ route('dashboard') }}" class="logo-con">
                <img src="{{ asset('assets/images/logo.png') }}" class="img-responsive center-block" alt="لوگو قالب مدیران">
            </a>
        </div><!-- /.header-right -->
        <div class="header-left">
            <div class="top-bar">
                <ul class="nav navbar-nav navbar-left">
                    <li class="dropdown dropdown-announces">
                        <a href="#" class="dropdown-toggle btn" data-bs-toggle="dropdown">
                            <i class="icon-bell"></i>
                            <span class="badge badge-warning">{{ Auth::user()->unreadNotifications->count() }}</span>
                        </a>
                        <ul class="dropdown-menu has-scrollbar">
                            <li class="dropdown-header clearfix">
                                <span class="float-start">
                                    <a href="#" rel="tooltip" title="خواندن همه" data-placement="left">
                                        <i class="icon-eye"></i>
                                    </a>
                                    <span>
                                        شما 1 اعلان تازه دارید.
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
                                            <p>اعلان نمونه</p>
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
                            <img src="{{ Auth::user()->image === 'noimage.png' ? 'assets/images/user/48.png' : Auth::user()->image }}"
                                alt="عکس پرفایل" class="img-circle img-responsive">
                            <span>{{ Auth::user()->name }}</span>
                            <i class="icon-arrow-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('profile') }}">
                                    <i class="icon-note"></i>
                                    ویرایش پروفایل
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li class="divider"></li>
                            <li>
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('خروج') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    class="d-none">
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
