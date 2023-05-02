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
<script src="{{ asset('assets/plugins/persian-date/persian-date.min.js') }}"></script>
<script src="{{ asset('assets/plugins/persian-datepicker/js/persian-datepicker.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/calendar.js') }}"></script>

{{-- <script src="https://cdn.ckeditor.com/ckeditor5/35.3.0/classic/ckeditor.js"></script>

<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            language: {
                ui: 'en',
                content: 'fa'
            },
            resize_dir: 'both'
        })

</script> --}}



<!-- END PAGE JAVASCRIPT -->

<!-- BEGIN PAGE JAVASCRIPT -->
<script src="{{ asset('assets/plugins/clockpicker/dist/bootstrap-clockpicker.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/clock.js') }}"></script>
<!-- END PAGE JAVASCRIPT -->

<script>
    $(document).ready(function() {
        const headers = new Headers({
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        });

        $('.confirm').on('click', function() {
            Swal.fire({
                title: "کد تایید ارسال شده به تلفن همراه خود را وارد کنید",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'حذف',
                cancelButtonText: 'لغو',
                html: `
                    <input class="form-control form-control-sm round" type="text" id="delete-modal-phone-verification" name="phone_verification" placeholder="کد تایید را وارد کنید ..."/>
                    <input class="form-control form-control-sm round mt-2" type="password" id="delete-modal-access-password" name="access_password" placeholder="رمز دسترسی خود را وارد کنید..."/>
                    `,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                didOpen: () => {
                    return fetch('/code/send', {
                            headers: headers
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        });
                },
                preConfirm: () => {
                    return fetch('/code/verify', {
                            headers: headers,
                            method: 'POST',
                            body: JSON.stringify({
                                'phone_verification': $(
                                        '#delete-modal-phone-verification')
                                    .val(),
                                'access_password': $(
                                    '#delete-modal-access-password').val()
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                'کد تایید یا رمز دسترسی اشتباه است')
                        });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit(this.title, this.id);
                    Swal.fire({
                        title: 'حذف شد!',
                        text: 'اطلاعات مورد نظر حذف شد',
                        icon: 'success',
                        confirmButtonText: 'باشه'
                    })
                }
            })
        })

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

        function showToast(event) {
            Toast.fire({
                icon: 'success',
                title: `${event.detail.message}`
            })
        }

        window.addEventListener('resourceModified', showToast);

        window.addEventListener('livewire-upload-start', (event) => {
            targetInputId = event.target
            progressBarContainer = targetInputId.nextElementSibling
            progressBarContainer.classList.remove('d-none')
            progressBarContainer.classList.add('d-block')
        })

        window.addEventListener('livewire-upload-error', (event) => {
            targetInputId = event.target
            progressBarContainer = targetInputId.nextElementSibling
            progressBarContainer.classList.remove('bg-success')
            progressBarContainer.classList.add('bg-danger')
        })

        window.addEventListener('livewire-upload-progress', (event) => {
            targetInputId = event.target
            progressBarContainer = targetInputId.nextElementSibling
            progressBar = progressBarContainer.firstElementChild
            progressBar.style.width = event.detail.progress + '%'
            progressBar.innerText = event.detail.progress + '%'
        })

        window.addEventListener('livewire-upload-finish', (event) => {
            targetInputId = event.target
            progressBarContainer = targetInputId.nextElementSibling
            progressBarContainer.classList.remove('d-block')
            progressBarContainer.classList.add('d-none')
        })
    })
</script>
@stack('js')

@livewireScripts

</body>

</html>
