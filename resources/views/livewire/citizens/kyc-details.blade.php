<div>
    <x-modal id="modal-kyc-{{ $kyc->id }}" title="جزئیات اطلاعات کاربر" size="modal-xl">
        <table class="table table-bordered table-hover table-striped text-center">
            <tbody>
                <tr>
                    <td>نام</td>
                    <td>{{ $kyc->fname }}</td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject" @disabled($kyc->status === 1 || array_key_exists('fname', $kyc_errors))>وارد کردن
                                دلیل
                                اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="fname_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('fname_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>نام خانوادگی</td>
                    <td>{{ $kyc->lname }}</td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="lname_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('lname_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>کد ملی</td>
                    <td>{{ $kyc->melli_code }}</td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="melli_code_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('melli_code_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>تاریخ تولد</td>
                    <td>{{ jdate($kyc->birthdate)->format('Y/m/d') }}</td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="birthdate_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('birthdate_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>استان </td>
                    <td>{{ $kyc->province }}</td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="province_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('province_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>تصویر کارت ملی</td>
                    <td>
                        <a href="{{ $kyc->melli_card }}" target="_blank">مشاهده</a>
                    </td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="melli_card_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('melli_card_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>فیلم احراز مستند</td>
                    <td>
                        <button type="button" class="btn btn-link"
                            id="kyc-video-btn-{{ $kyc->id }}">مشاهده</button>
                    </td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="video_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('video_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td>جنسیت</td>
                    <td>{{ $kyc->gender }}</td>
                    @if ($kyc->status == 0)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="gender_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('gender_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>

        @production
            <x-form.verification />
        @endproduction

        <x-slot name='footer'>
            @if ($kyc->status == 0)
                <button class="w-50 btn btn-primary round" wire:click="save">ثبت</button>
            @endif
            <button class="btn btn-danger round w-25 mx-auto" data-bs-dismiss="modal">بستن</button>
        </x-slot>
    </x-modal>
</div>

@assets
    <style>
        .form-box {
            position: relative;
            overflow: none;
        }

        .textarea {
            width: 100%;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 100;
            display: none;
        }
    </style>
@endassets

@script
    <script>
        $('.reject').on('click', function(e) {
            let el = event.target;
            let parent = $(el).parent();
            $(parent).children('.textarea').css('display', 'block');
        })

        $('.close-btn').on('click', function(event) {
            let el = event.target;
            $(el).parent().parent().parent().css('display', 'none');
        })

        $('#kyc-video-btn-{{ $kyc->id }}').on('click', function() {
            console.log('clicked');
            Swal.fire({
                title: 'فیلم احراز مستند',
                html: `
                    <video width="320" height="240" controls>
                        <source src="{{ $kyc->video }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <h4>{{ $kyc->user->code }}</h4>
                    <p>{{ $kyc->verifyText->text ?? '' }}</p>
                `,
                showCloseButton: true,
                showConfirmButton: false,
            });
        });
    </script>
@endscript
