<!DOCTYPE html>
<html lang="fa" dir="rtl" class="rtl">

<head>
    <title>پنل مدیریت سامانه متارنگ | {{ $title ?? 'Page Title' }}</title>
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

    @stack('css')

    {{-- @vite('resources/css/app.css')
    @vite('resources/js/app.js') --}}

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
                <img src="{{ asset('assets/images/logo.png') }}" class="img-responsive center-block"
                    alt="لوگو قالب مدیران">
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

    <!-- BEGIN WRAPPER -->
    <div id="wrapper">
        <!-- BEGIN SIDEBAR -->
        <div id="sidebar">
            <div class="sidebar-top">
                <div class="search-box">
                    <form class="search-form" action="#" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control placeholder-light" placeholder="جستجو..."
                                name="key">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-round submit">
                                    <i class="icon-magnifier"></i>
                                </button>
                            </span>
                        </div>
                    </form>
                </div><!-- /.search-box -->
            </div><!-- /.sidebar-top -->
            <div class="side-menu-container">
                <ul class="metismenu" id="side-menu">
                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i class="icon-home"></i>
                            <span>داشبورد</span>
                        </a>
                    </li>
                    @hasanyrole('citizens-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="icon-user"></i>
                                <span>شهروندان</span>
                            </a>
                            <ul>
                                @can('view-registration-info')
                                    <li>
                                        <a href="{{ route('citizens.registration-info') }}" class="">
                                            <i class="fa fa-address-card"></i>
                                            <span>مشخصات ثبت نام</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('verify-kyc')
                                    <li>
                                        <a href="{{ route('citizens.kycs') }}" class="">
                                            <i class="fa fa-check-circle"></i>
                                            <span>احراز هویت</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('verify-bank-accounts')
                                    <li>
                                        <a href="{{ route('citizens.bank-accounts') }}" class="">
                                            <i class="fa fa-bank"></i>
                                            <span>حساب های بانکی</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-deposits')
                                    <li>
                                        <a href="{{ route('citizens.deposits') }}" class="">
                                            <i class="fa fa-money"></i>
                                            <span>واریزی ها</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-withdraws')
                                    <li>
                                        <a href="{{ route('citizens.withdraws') }}" class="">
                                            <i class="fa fa-dollar"></i>
                                            <span>برداشت ها</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-profile-details')
                                    <li>
                                        <a href="{{ route('citizens.profile-details') }}" class="">
                                            <i class="fa fa-user-circle"></i>
                                            <span>جزئیات پروفایل</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-assets')
                                    <li>
                                        <a href="{{ route('citizens.assets') }}" class="">
                                            <i class="fa fa-pie-chart"></i>
                                            <span>دارایی ها</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    @hasanyrole('features-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-cube"></i>
                                <span>زمین ها</span>
                            </a>
                            <ul>
                                @can('manage-features-info')
                                    <li>
                                        <a href="{{ route('features.all') }}" class="">
                                            <i class="fa fa-cubes"></i>
                                            <span>کل زمین ها</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-features-prices')
                                    <li>
                                        <a href="{{ route('features.prices') }}" class="">
                                            <i class="fa fa-dollar"></i>
                                            <span>قیمت زمین ها</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-sold-features')
                                    <li>
                                        <a href="{{ route('features.sold') }}" class="">
                                            <i class="fa fa-money"></i>
                                            <span>زمین های فروخته شده</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-features-trades')
                                    <li>
                                        <a href="{{ route('features.trades') }}" class="">
                                            <i class="fa fa-exchange"></i>
                                            <span>مبادله زمین</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('view-priced-features')
                                    <li>
                                        <a href="{{ route('features.priced') }}" class="">
                                            <i class="fa fa-won"></i>
                                            <span>قیمت گذاری زمین</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-features-pricing-limits')
                                    <li>
                                        <a href="{{ route('features.pricing-limits') }}" class="">
                                            <i class="fa fa-won"></i>
                                            <span>محدودیت های قیمت گذاری</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-features-limits')
                                    <li>
                                        <a href="{{ route('features.limits') }}" class="">
                                            <i class="fa fa-won"></i>
                                            <span>محدودیت های املاک</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    @can('access-management')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="icon-key"></i>
                                <span>مدیریت دسترسی ها</span>
                            </a>
                            <ul>

                                <li>
                                    <a href="{{ route('access-management.employees') }}" class="">
                                        <i class="fa fa-user-plus"></i>
                                        <span>مدیران</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('access-management.roles') }}" class="">
                                        <i class="fa fa-handshake-o"></i>
                                        <span>مسئولیت ها</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('access-management.permissions') }}" class="">
                                        <i class="fa fa-gears"></i>
                                        <span>دسترسی ها</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    @hasanyrole('employees-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-user"></i>
                                <span>مدیریت کارکنان</span>
                            </a>
                            <ul>
                                @can('manage-employee-info')
                                    <li>
                                        <a href="{{ route('employees.info') }}" class="">
                                            <i class="fa fa-drivers-license-o"></i>
                                            <span>مشخصات حقیقی</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-employee-bank-accounts')
                                    <li>
                                        <a href="{{ route('employees.bank-info') }}" class="">
                                            <i class="fa fa-bank"></i>
                                            <span>اطلاعات بانکی</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-employee-documents')
                                    <li>
                                        <a href="{{ route('employees.documents') }}" class="">
                                            <i class="icon-docs"></i>
                                            <span>اسناد</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-employee-salary')
                                    <li>
                                        <a href="{{ route('employees.salary') }}" class="">
                                            <i class="fa fa-money"></i>
                                            <span>حقوق و دستمزد</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-employee-time-card')
                                    <li>
                                        <a href="{{ route('employees.time-card') }}" class="">
                                            <i class="fa fa-hourglass-end"></i>
                                            <span>کارت زمان</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-employee-tasks')
                                    <li>
                                        <a href="{{ route('employees.tasks') }}" class="">
                                            <i class="fa fa-handshake-o"></i>
                                            <span>وظایف محوله</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    @hasanyrole('support-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-phone"></i>
                                <span>پشتیبانی</span>
                            </a>
                            <ul>
                                @can('respond-to-citziens-safety-tickets')
                                    <li>
                                        <a href="{{ route('support.citizens-safety') }}" class="">
                                            <i class="fa fa-universal-access"></i>
                                            <span>امنیت شهروندان</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('respond-to-technical-support-tickets')
                                    <li>
                                        <a href="{{ route('support.technical-support') }}" class="">
                                            <i class="fa fa fa-gears"></i>
                                            <span>پشتیبانی فنی</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('respond-to-investment-tickets')
                                    <li>
                                        <a href="{{ route('support.investment') }}" class="">
                                            <i class="fa fa-line-chart"></i>
                                            <span>سرمایه گذاری</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('respond-to-inspection-tickets')
                                    <li>
                                        <a href="{{ route('support.inspection') }}" class="">
                                            <i class="fa fa-shield"></i>
                                            <span>بازرسی</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('respond-to-protection-tickets')
                                    <li>
                                        <a href="{{ route('support.protection') }}" class="">
                                            <i class="fa fa-user-secret"></i>
                                            <span>حراست</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('respond-to-ztb-management-tickets')
                                    <li>
                                        <a href="{{ route('support.ztb-management') }}" class="">
                                            <i class="fa fa-gavel"></i>
                                            <span>مدیریت کل ز.ت.ب</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    @hasanyrole('store-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-shopping-cart"></i>
                                <span>فروشگاه</span>
                            </a>
                            <ul>
                                @can('manage-packages')
                                    <li>
                                        <a href="{{ route('store.packages') }}" class="">
                                            <i class="fa fa-shopping-cart"></i>
                                            <span>بسته ها</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-currencies')
                                    <li>
                                        <a href="{{ route('store.currencies') }}" class="">
                                            <i class="fa fa-euro"></i>
                                            <span>ارزها</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    @hasanyrole('dynasty-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-users"></i>
                                <span>سلسله</span>
                            </a>
                            <ul>
                                @can('manage-dynasty-prizes')
                                    <li>
                                        <a href="{{ route('dynasty.prizes') }}" class="">
                                            <i class="fa fa-money"></i>
                                            <span>جوایزه سلسله </span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-dynasty-messages')
                                    <li>
                                        <a href="{{ route('dynasty.messages') }}" class="">
                                            <i class="fa fa-comment"></i>
                                            <span>پیام های سلسله</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-dynasty-permissions')
                                    <li>
                                        <a href="{{ route('dynasty.permissions') }}" class="">
                                            <i class="fa fa-check-square"></i>
                                            <span>دسترسی ها</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    @hasanyrole('maps-management|super-admin')
                        <li>
                            <a href="{{ route('map-management') }}">
                                <span class="fa fa-map"></span>
                                <span>مدیریت نقشه ها</span>
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('level-management|super-admin')
                        <li>
                            <a href="{{ route('level') }}">
                                <span class="fa fa-level-up"></span>
                                <span>مدیریت سطح</span>
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('ip-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-wifi"></i>
                                <span>مدیریت IP</span>
                            </a>
                            <ul>
                                @can('manage-ip-ranges')
                                    <li>
                                        <a href="{{ route('ip.ranges') }}" class="">
                                            <i class="fa fa-signal"></i>
                                            <span>رنج آی پی</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-api-allowed-ips')
                                    <li>
                                        <a href="{{ route('ip.api') }}" class="">
                                            <i class="fa fa-sort-amount-asc"></i>
                                            <span>دسترسی های Api</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-admin-allowed-ips')
                                    <li>
                                        <a href="{{ route('ip.admin') }}" class="">
                                            <i class="fa fa-sign-in"></i>
                                            <span>دسترسی پنل ادمین</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('manage-ip-whitelisting')
                                    <li>
                                        <a href="{{ route('ip.white-listing') }}" class="">
                                            <i class="fa fa-sign-in"></i>
                                            <span>درخواستهای رفع محدودیت</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    @hasanyrole('calendar-management|super-admin')
                        <li>
                            <a href="{{ route('calendar') }}">
                                <span class="fa fa-calendar"></span>
                                <span>تقویم</span>
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('versions-management|super-admin')
                        <li>
                            <a href="{{ route('versions') }}">
                                <span class="fa fa-list"></span>
                                <span>ورژن ها</span>
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('reports-management|super-admin')
                        <li>
                            <a href="{{ route('reports') }}">
                                <span class="fa fa-eye"></span>
                                <span>گزارشات کاربران</span>
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('system-variables-management|super-admin')
                        <li>
                            <a href="{{ route('system-variables') }}">
                                <span class="fa fa-puzzle-piece"></span>
                                <span>متغیرهای سیستم</span>
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('challenge-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-question"></i>
                                <span>چالش پرسش و پاسخ</span>
                            </a>
                            <ul>
                                <li>
                                    <a href="{{ route('challenge') }}" class="">
                                        <i class="fa fa-list"></i>
                                        <span>لیست سوالات</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endhasanyrole
                    @hasanyrole('tutorials-management|super-admin')
                        <li>
                            <a href="#" class="dropdown-toggle">
                                <i class="fa fa-video"></i>
                                <span>فیلم های آموزشی</span>
                            </a>
                            <ul>
                                <li>
                                    <a href="{{ route('videos') }}" class="">
                                        <i class="fa fa-list"></i>
                                        <span>ویدیوها</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('video.categories') }}" class="">
                                        <i class="fa fa-list"></i>
                                        <span>دسته بندی ویدئوها</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endhasanyrole

                    @hasanyrole('translations-management|super-admin')
                        <li>
                            <a href="{{ route('translations') }}">
                                <span class="fa fa-list"></span>
                                <span>ترجمه</span>
                            </a>
                        </li>
                    @endhasanyrole

                    @hasanyrole('isic-codes-management|super-admin')
                        <li>
                            <a href="{{ route('isic-codes') }}">
                                <span class="fa fa-list"></span>
                                <span>کدهای ISIC</span>
                            </a>
                        </li>
                    @endhasanyrole
                </ul><!-- /#side-menu -->
            </div><!-- /.side-menu-container -->
        </div><!-- /#sidebar -->
        <!-- END SIDEBAR -->

        <!-- BEGIN PAGE CONTENT -->
        <div id="page-content">
            <div class="row">
                <div class="col-12">
                    <div class="portlet box border shadow">
                        <div class="portlet-heading">
                            <div class="portlet-title">
                                <h3 class="title">
                                    <i class="icon-note"></i>
                                    {{ $title ?? 'Page Title' }}
                                </h3>
                            </div><!-- /.portlet-title -->
                            <div class="buttons-box">
                                <a class="btn btn-sm btn-default btn-round" rel="tooltip" title="آموزش"
                                    href="#video-tutorials-modal">
                                    <i class="icon-question"></i>
                                </a>
                            </div><!-- /.buttons-box -->
                        </div><!-- /.portlet-heading -->
                        <div class="portlet-body">
                            {{ $slot }}
                        </div><!-- /.portlet-body -->
                    </div><!-- /.portlet -->
                </div><!-- /.col-12 -->
            </div><!-- /.row -->
        </div><!-- /#page-content -->
        <!-- END PAGE CONTENT -->

    </div><!-- /#wrapper -->
    <!-- END WRAPPER -->

    <!-- BEGIN JS -->
    <script src="{{ asset('assets/plugins/jquery/dist/jquery-3.1.0.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/metisMenu/dist/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/paper-ripple/dist/PaperRipple.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js') }}">
    </script>
    <script src="{{ asset('assets/plugins/screenfull/dist/screenfull.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/switchery/dist/switchery.js') }}"></script>
    <script src="{{ asset('assets/js/core.js') }}"></script>
    <script src="{{ asset('assets/plugins/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/morris.js/morris.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-incremental-counter/jquery.incremental-counter.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/ammap3/ammap/ammap.js') }}"></script>
    <script src="{{ asset('assets/plugins/ammap3/ammap/maps/js/iranHighFa.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-knob/dist/jquery.knob.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/dashboard1.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/resumable.js/1.1.0/resumable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/plugins/ckeditor-full/ckeditor.js') }}"></script>
    <!-- END JS -->

    <script>
        document.addEventListener('start-countdown', (event) => {
            const sendSMSBtn = document.getElementById(event.detail.id);
            let countdownIntervalId;
            const countdownTime = event.detail.countdownTime;

            // Disable the button and change its text to the countdown
            sendSMSBtn.disabled = true;
            sendSMSBtn.innerText = `ارسال مجدد بعد از ${countdownTime} ثانیه`;

            // Start the countdown interval
            let remainingTime = countdownTime;
            countdownIntervalId = setInterval(() => {
                remainingTime -= 1;
                sendSMSBtn.innerText = `ارسال مجدد بعد از ${remainingTime} ثانیه`;

                // When the countdown is finished, re-enable the button
                if (remainingTime === 0) {
                    clearInterval(countdownIntervalId);
                    sendSMSBtn.disabled = false;
                    sendSMSBtn.innerText = 'ارسال کد تایید';
                }
            }, 1000);
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-right',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        window.addEventListener('notify', (event) => {
            Toast.fire({
                icon: event.detail.type ? event.detail.type : 'success',
                title: `${event.detail.message}`
            })
        });

        document.addEventListener('livewire-upload-start', function(event) {
            let progressBarContainer = event.target.nextElementSibling;
            progressBarContainer.classList.remove('d-none');
        });

        document.addEventListener('livewire-upload-progress', function(event) {
            let progressBar = event.target.nextElementSibling.querySelector('.progress-bar');
            progressBar.style.width = event.detail.progress + '%';
            progressBar.innerText = event.detail.progress + '%';
        });

        document.addEventListener('livewire-upload-finish', function() {
            let progressBarContainer = event.target.nextElementSibling;
            progressBarContainer.classList.add('d-none');
        });
    </script>
    @stack('scripts')
</body>

</html>
