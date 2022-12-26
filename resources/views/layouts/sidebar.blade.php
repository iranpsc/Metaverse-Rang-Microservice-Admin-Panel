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
                <a href="#" class="dropdown-toggle">
                    <i class="icon-user"></i>
                    <span>شهروندان</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('citizens.registration-info') }}" class="">
                            <i class="icon-settings"></i>
                            <span>مشخصات ثبت نام</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('citizens.kyc') }}" class="">
                            <i class="icon-settings"></i>
                            <span>احراز هویت</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('citizens.bank-accounts') }}" class="">
                            <i class="icon-settings"></i>
                            <span>حساب های بانکی</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('citizens.deposits') }}" class="">
                            <i class="icon-settings"></i>
                            <span>واریزی ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('citizens.withdraws') }}" class="">
                            <i class="icon-settings"></i>
                            <span>برداشت ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('citizens.profile-details') }}" class="">
                            <i class="icon-settings"></i>
                            <span>جزئیات پروفایل</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('citizens.assets') }}" class="">
                            <i class="icon-settings"></i>
                            <span>دارایی ها</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-notebook"></i>
                    <span>زمین ها</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('features.all') }}" class="">
                            <i class="icon-settings"></i>
                            <span>کل زمین ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('features.prices') }}" class="">
                            <i class="icon-settings"></i>
                            <span>قیمت زمین ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('features.sold') }}" class="">
                            <i class="icon-settings"></i>
                            <span>زمین های فروخته شده</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('features.trades') }}" class="">
                            <i class="icon-settings"></i>
                            <span>مبادله زمین</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('features.priced') }}" class="">
                            <i class="icon-settings"></i>
                            <span>قیمت گذاری زمین</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('features.pricing-limits') }}" class="">
                            <i class="icon-settings"></i>
                            <span>محدودیت قیمت گذاری</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-key"></i>
                    <span>مدیریت دسترسی ها</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('access-management.employees') }}" class="">
                            <i class="icon-settings"></i>
                            <span>مسئولیت ها و دسترسی های کارمندان</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('access-management.roles') }}" class="">
                            <i class="icon-settings"></i>
                            <span>مسئولیت ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('access-management.permissions') }}" class="">
                            <i class="icon-settings"></i>
                            <span>دسترسی ها</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-user"></i>
                    <span>مدیریت کارکنان</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('employees.info') }}" class="">
                            <i class="icon-settings"></i>
                            <span>مشخصات حقیقی</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('employees.bank-info') }}" class="">
                            <i class="icon-settings"></i>
                            <span>اطلاعات بانکی</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('employees.documents') }}" class="">
                            <i class="icon-settings"></i>
                            <span>اسناد</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('employees.salary') }}" class="">
                            <i class="icon-settings"></i>
                            <span>حقوق و دستمزد</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('employees.time-card') }}" class="">
                            <i class="icon-settings"></i>
                            <span>کارت زمان</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('employees.tasks') }}" class="">
                            <i class="icon-settings"></i>
                            <span>وظایف محوله</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-user"></i>
                    <span>پشتیبانی</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('support.citizens-safety') }}" class="">
                            <i class="icon-settings"></i>
                            <span>امنیت شهروندان</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('support.technical-support') }}" class="">
                            <i class="icon-settings"></i>
                            <span>پشتیبانی فنی</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('support.investment') }}" class="">
                            <i class="icon-settings"></i>
                            <span>سرمایه گذاری</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('support.inspection') }}" class="">
                            <i class="icon-settings"></i>
                            <span>بازرسی</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('support.protection') }}" class="">
                            <i class="icon-settings"></i>
                            <span>حراست</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('support.ztb-management') }}" class="">
                            <i class="icon-settings"></i>
                            <span>مدیریت کل ز.ت.ب</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-store"></i>
                    <span>فروشگاه</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('store.packages') }}" class="">
                            <i class="icon-settings"></i>
                            <span>فروشگاه</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('store.currencies') }}" class="">
                            <i class="icon-settings"></i>
                            <span>ارزها</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle">
                    <i class="icon-store"></i>
                    <span>سلسله</span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('dynasty.prizes') }}" class="">
                            <i class="icon-settings"></i>
                            <span>پیام های سلسله</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dynasty.messages') }}" class="">
                            <i class="icon-settings"></i>
                            <span>پیام های سلسله</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dynasty.permissions') }}" class="">
                            <i class="icon-settings"></i>
                            <span>دسترسی ها</span>
                        </a>
                    </li>
                </ul>
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
