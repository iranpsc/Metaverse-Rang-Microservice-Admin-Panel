@include('layouts.header')

<!-- BEGIN WRAPPER -->
<div id="wrapper">
    {{-- CHANGE PASSWORD MODAL --}}
    @include('layouts.sidebar')
    <!-- BEGIN PAGE CONTENT -->
    <div id="page-content">
        <div class="row">
            <!-- BEGIN BREADCRUMB -->
            <div class="col-md-12">
                <div class="breadcrumb-box border shadow">
                    <ul class="breadcrumb">
                        <li><a href="dashboard.html">پیشخوان</a></li>
                        <li><a href="#">صفحات</a></li>
                        <li class="active">صفحه خام</li>
                    </ul>
                    <div class="breadcrumb-left">
                        {{ \Morilog\Jalali\Jalalian::now()->format('Y-m-d') }}
                        <i class="icon-calendar"></i>
                        <i id="clock"></i>
                        <i class="icon-clock"></i>
                    </div><!-- /.breadcrumb-left -->
                </div><!-- /.breadcrumb-box -->
            </div><!-- /.col-md-12 -->
            <!-- END BREADCRUMB -->

            <div class="col-12">
                <div class="portlet box border shadow">
                    <div class="portlet-heading">
                        <div class="portlet-title">
                            <h3 class="title">
                                <i class="icon-note"></i>
                                داشبورد
                            </h3>
                        </div><!-- /.portlet-title -->
                        <div class="buttons-box">
                            <a class="btn btn-sm btn-default btn-round btn-fullscreen" rel="tooltip" title="تمام صفحه" href="#">
                                <i class="icon-size-fullscreen"></i>
                            </a>
                            <a class="btn btn-sm btn-default btn-round btn-collapse" rel="tooltip" title="کوچک کردن" href="#">
                                <i class="icon-arrow-up"></i>
                            </a>
                        </div><!-- /.buttons-box -->
                    </div><!-- /.portlet-heading -->
                    <div class="portlet-body">
                        @yield('content')
                    </div><!-- /.portlet-body -->
                </div><!-- /.portlet -->
            </div><!-- /.col-12 -->
        </div><!-- /.row -->
    </div><!-- /#page-content -->
    <!-- END PAGE CONTENT -->

</div><!-- /#wrapper -->
<!-- END WRAPPER -->

@push('js')
    <script>
        function time() {
            var h,m,s;
            var clock = document.getElementById('clock');

            setInterval(() => {
                var t = new Date();

                h = t.getHours();
                m = t.getMinutes();
                s = t.getSeconds();

                if(h < 10) {
                    h = "0"+h;
                }
                if(m < 10) {
                    m = "0"+m;
                }
                if(s < 10) {
                    s = "0"+s;
                }
                clock.innerText = h + " : " + m + " : " + s;
            }, 1000);

        }
        time();
    </script>
@endpush

@include('layouts.footer')

