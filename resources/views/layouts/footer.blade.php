<!-- BEGIN SETTING -->
<div class="settings d-none d-sm-block">
    <a href="#" class="btn" id="toggle-setting">
        <i class="icon-settings"></i>
    </a>
    <h3 class="text-center">تنظیمات</h3>

    <div class="fix-header-box">
        <p class="h6">
            هدر ثابت:
            <span class="pull-left">
                <input type="checkbox" class="fix-header-switch normal" checked>
            </span>
        </p>
    </div><!-- /.fix-header-box -->
    <hr class="light">
    <div class="toggle-sidebar-box">
        <p class="h6">
            جمع کردن سایدبار:
            <span class="pull-left">
                <input type="checkbox" class="toggle-sidebar-switch normal">
            </span>
        </p>
    </div><!-- /.toggle-sidebar-box -->
    <hr class="light">
    <div class="toggle-sidebar-box">
        <p class="h6">
            سایدبار خلاقانه:
            <span class="pull-left">
                <input type="checkbox" class="creative-sidebar-switch normal">
            </span>
        </p>
    </div><!-- /.toggle-sidebar-box -->
    <hr class="light">
    <div class="theme-colors">
        <p class="h6">رنگ قالب : </p>
        <a class="btn btn-round btn-blue ripple-effect active" data-color="blue"></a>
        <a class="btn btn-round btn-red ripple-effect" data-color="red"></a>
        <a class="btn btn-round btn-green ripple-effect" data-color="green"></a>
        <a class="btn btn-round btn-orange ripple-effect" data-color="orange"></a>
        <a class="btn btn-round btn-purple ripple-effect" data-color="purple"></a>
        <a class="btn btn-round btn-deeporange ripple-effect" data-color="deeporange"></a>
        <a class="btn btn-round btn-cyan ripple-effect" data-color="cyan"></a>
        <a class="btn btn-round btn-rose ripple-effect" data-color="rose"></a>
        <a class="btn btn-round btn-lime ripple-effect" data-color="lime"></a>
        <a class="btn btn-round btn-darkorange ripple-effect" data-color="darkorange"></a>
    </div><!-- /.theme-colors -->
    <div class="theme-code ltr text-left">
        <code></code>
    </div><!-- /.theme-code -->
</div><!-- /.settings -->
<!-- END SETTING -->

<!-- BEGIN CODE MODAL -->
<div class="modal fade" id="code-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn btn-default btn-round btn-icon float-start" id="btn-copy"><i
                        class="fa fa-copy"></i></button>
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">کپی کردن کدها</h4>
            </div>
            <div class="modal-body"></div>
        </div> <!-- /.modal-content -->
    </div> <!-- /.modal-dialog -->
</div> <!-- /.modal -->
<!-- END CODE MODAL -->

<!-- BEGIN JS -->
<script src="{{ asset('assets/plugins/jquery/dist/jquery-3.1.0.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/plugins/metisMenu/dist/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/plugins/paper-ripple/dist/PaperRipple.min.js') }}"></script>
<script src="{{ asset('assets/plugins/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js') }}">
</script>
<script src="{{ asset('assets/plugins/screenfull/dist/screenfull.min.js') }}"></script>
<script src="{{ asset('assets/plugins/iCheck/icheck.min.js') }}"></script>
<script src="{{ asset('assets/plugins/switchery/dist/switchery.js') }}"></script>
<script src="{{ asset('assets/js/core.js') }}"></script>
<!-- END JS -->
<!-- BEGIN PAGE JAVASCRIPT -->
<script src="{{ asset('assets/plugins/raphael/raphael.min.js') }}"></script>
<script src="{{ asset('assets/plugins/morris.js/morris.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-incremental-counter/jquery.incremental-counter.min.js') }}"></script>
<script src="{{ asset('assets/plugins/ammap3/ammap/ammap.js') }}"></script>
<script src="{{ asset('assets/plugins/ammap3/ammap/maps/js/iranHighFa.js') }}"></script>
<script src="{{ asset('assets/plugins/kamadatepicker/kamadatepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-knob/dist/jquery.knob.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/dashboard1.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/dist/js/i18n/fa.js') }}"></script>
<script src="{{ asset('assets/js/pages/select2.js') }}"></script>
<script src="{{ asset('assets/plugins/datepicker/jquery-ui-1.10.1.custom.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datepicker/jquery.ui.datepicker-cc.js') }}"></script>
<script src="{{ asset('assets/plugins/datepicker/calendar.js') }}"></script>
<script src="{{ asset('assets/plugins/datepicker/jquery.ui.datepicker-cc-fa.js') }}"></script>
<script src="{{ asset('assets/js/pages/calendar-ui.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2@11.js') }}"></script>
<!-- END PAGE JAVASCRIPT -->

<script>
    $(document).ready(function() {
        $('.confirm').on('click', function() {
            Swal.fire({
                title: 'آیا می خواهید حذف کنید؟',
                text: "بعد از حذف قادر به بازیابی نخواهید بود!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله، حذف کن!',
                cancelButtonText: 'لغو',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit(this.title, this.id);
                    Swal.fire(
                        'حذف شد!',
                        'اطلاعات مورد نظر حذف شد',
                        'success',
                    )
                }
            })
        })
    })
</script>
@stack('js')

@livewireScripts

</body>

</html>
