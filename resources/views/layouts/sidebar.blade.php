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
                 <img src="{{ Auth::user()->image === 'noimage.png' ? 'assets/images/user/128.png' : Auth::user()->image }}"
                     alt="عکس پروفایل" class="img-circle img-responsive">
             </a>
             <div class="user-details">
                 <h4>{{ Auth::user()->name }}</h4>
                 <p class="role">مدیر سایت</p>
                 <div class="dropdown user-login">
                     <button class="btn btn-xs btn-status dropdown-toggle btn-round" type="button"
                         data-bs-toggle="dropdown" data-hover="dropdown">
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
                                 <a href="{{ route('citizens.kyc') }}" class="">
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
                                 </l•••••i>
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
                         @can('manage-pricing-limits')
                             <li>
                                 <a href="{{ route('features.pricing-limits') }}" class="">
                                     <i class="fa fa-bar-chart"></i>
                                     <span>محدودیت قیمت گذاری</span>
                                 </a>
                             </li>
                         @endcan
                         @can('manage-feature-limits')
                             <li>
                                 <a href="{{ route('features.limits') }}" class="">
                                     <i class="fa fa-bar-chart"></i>
                                     <span>محدودیت املاک</span>
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
                                 <span>مسئولیت ها و دسترسی های کارمندان</span>
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
                                     <span>فروشگاه</span>
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
             @hasanyrole('statistics-management|super-admin')
                 <li>
                     <a href="{{ route('statistics') }}">
                         <span class="icon-note"></span>
                         <span>آمار سراسری</span>
                     </a>
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
             @hasanyrole('music-management|super-admin')
                 <li>
                     <a href="#" class="dropdown-toggle">
                         <i class="fa fa-music"></i>
                         <span>مدیریت موسیقی سراسری</span>
                     </a>
                     <ul>
                         <li>
                             <a href="{{ route('music') }}" class="">
                                 <i class="fa fa-list"></i>
                                 <span>لیست آهنگ ها</span>
                             </a>
                         </li>
                         <li>
                             <a href="{{ route('music.categories') }}" class="">
                                 <i class="fa fa-category"></i>
                                 <span>دسته بندی ها</span>
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
         </ul><!-- /#side-menu -->
     </div><!-- /.side-menu-container -->
 </div><!-- /#sidebar -->
