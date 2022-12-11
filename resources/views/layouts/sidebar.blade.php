 <!-- BEGIN SIDEBAR -->
 <div id="sidebar">
    <div class="sidebar-top">
        <div class="search-box">
            <form class="search-form" action="#" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control placeholder-light" placeholder="جستجو..." name="key">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-round submit">
                            <i class="icon-magnifier"></i>
                        </button>
                    </span>
                </div>
            </form>
        </div><!-- /.search-box -->
        <div class="user-box">
            <a href="#">
                <img src="assets/images/user/128.png" alt="عکس پروفایل" class="img-circle img-responsive">
            </a>
            <div class="user-details">
                <h4>{{ Auth::user()->name }}</h4>
                <p class="role">مدیر سایت</p>
                <div class="dropdown user-login">
                    <button class="btn btn-xs btn-status dropdown-toggle btn-round" type="button" data-bs-toggle="dropdown" data-hover="dropdown">
                        <i class="fa fa-circle text-success"></i>
                        <span>دردسترس</span>
                    </button>
                    <ul class="dropdown-menu dropdown-status">
                        <li>
                            <a href="#" class="busy">
                                <i class="fa fa-circle text-success"></i>
                                <span>دردسترس</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="busy">
                                <i class="fa fa-circle text-danger"></i>
                                <span>مشغول</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-circle text-gray"></i>
                                <span>مخفی</span>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fa fa-circle text-warning"></i>
                                <span>سایر</span>
                            </a>
                        </li>
                    </ul>
                </div><!-- /dropdown -->
            </div><!-- /.user-details -->
        </div><!-- /.user-box -->
    </div><!-- /.sidebar-top -->
    <div class="side-menu-container">
        <ul class="metismenu" id="side-menu">
            <li>
                <a href="{{ route('dashboard') }}">
                    <i class="icon-home"></i>
                    <span>داشبورد</span>
                </a>
            </li>
            <li>
                <a href="{{ route('citizens') }}">
                    <span class="icon-user"></span>
                    <span>شهروندان</span>
                </a>
            </li>
            <li>
                <a href="{{ route('access-management') }}">
                    <span class="icon-user"></span>
                    <span>مدیریت دسترسی ها</span>
                </a>
            </li>
            <li>
                <a href="{{ route('lands') }}">
                    <span class="icon-user"></span>
                    <span>زمین ها</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employees') }}">
                    <span class="fa fa-users"></span>
                    <span>مدیریت کارکنان</span>
                </a>
            </li>
            <li>
                <a href="{{ route('support') }}">
                    <span class="fa fa-envelope-o"></span>
                    <span>پشتیبانی</span>
                </a>
            </li>
            <li>
                <a href="{{ route('variables') }}">
                    <span class="icon-note"></span>
                    <span>محصولات فروشگاه</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dynasty') }}">
                    <span class="icon-note"></span>
                    <span>مدیریت سلسله</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <span class="icon-note"></span>
                    <span>آمار سراسری</span>
                </a>
            </li>
            <li>
                <a href="{{ route('map-management') }}">
                    <span class="fa fa-cart-plus"></span>
                    <span>مدیریت نقشه ها</span>
                </a>
            </li>
            <li>
                <a href="{{ route('level') }}">
                    <span class="fa fa-cart-plus"></span>
                    <span>مدیریت سطح</span>
                </a>
            </li>
            <li>
                <a href="{{ route('ip-management') }}">
                    <span class="fa fa-cart-plus"></span>
                    <span>مدیریت IP</span>
                </a>
            </li>
            <li>
                <a href="{{ route('calendar') }}">
                    <span class="fa fa-cart-plus"></span>
                    <span>تقویم</span>
                </a>
            </li>
            <li>
                <a href="{{ route('reports') }}">
                    <span class="fa fa-cart-plus"></span>
                    <span>گزارشات کاربران</span>
                </a>
            </li>
            <li>
                <a href="{{ route('system-variables') }}">
                    <span class="fa fa-cart-plus"></span>
                    <span>متغیرهای سیستم</span>
                </a>
            </li>
        </ul><!-- /#side-menu -->
    </div><!-- /.side-menu-container -->
</div><!-- /#sidebar -->
