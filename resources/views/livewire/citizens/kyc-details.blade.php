<div>
    <x-modal id="modal-kyc-{{ $kyc->id }}" title="جزئیات اطلاعات کاربر" size="modal-xl">
        <table class="table table-bordered table-hover table-striped text-center">
            <tbody>
                <tr>
                    <td>نام</td>
                    <td>{{ $kyc->fname }}</td>
                    @unless ($kyc->status == 1)
                    <td class="form-box">
                        <button class="btn btn-danger btn-sm round reject" @disabled($kyc->status === 1 || array_key_exists('fname', $kyc_errors))>وارد کردن دلیل اشکال</button>
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
                    @endunless
                </tr>
                <tr>
                    <td>نام خانوادگی</td>
                    <td>{{ $kyc->lname }}</td>
                    @unless ($kyc->status == 1)
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
                    @endunless
                </tr>
                <tr>
                    <td>نام پدر</td>
                    <td>{{ $kyc->father_name }}</td>
                    @unless ($kyc->status == 1)
                    <td class="form-box">
                        <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                        <div class="textarea">
                            <div class="card">
                                <div class="card-body">
                                    <textarea wire:model="father_name_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary round btn-sm save"
                                        wire:click="save_errors('father_name_err' )">ثبت</button>
                                    <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                </div>
                            </div>
                        </div>
                    </td>
                    @endunless
                </tr>
                <tr>
                    <td>کد ملی</td>
                    <td>{{ $kyc->melli_code }}</td>
                    @unless ($kyc->status == 1)
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
                    @endunless
                </tr>
                <tr>
                    <td>استان </td>
                    <td>{{ $kyc->province }}</td>
                    @unless ($kyc->status == 1)
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
                    @endunless
                </tr>
                <tr>
                    <td>شهر</td>
                    <td>{{ $kyc->city }}</td>
                    @unless ($kyc->status == 1)
                    <td class="form-box">
                        <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                        <div class="textarea">
                            <div class="card">
                                <div class="card-body">
                                    <textarea wire:model="city_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary round btn-sm save"
                                        wire:click="save_errors('city_err')">ثبت</button>
                                    <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                </div>
                            </div>
                        </div>
                    </td>
                    @endunless
                </tr>
                <tr>
                    <td>پلاک</td>
                    <td>{{ $kyc->number }}</td>
                    @unless ($kyc->status == 1)
                    <td class="form-box">
                        <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                        <div class="textarea">
                            <div class="card">
                                <div class="card-body">
                                    <textarea wire:model="number_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary round btn-sm save"
                                        wire:click="save_errors('number_err')">ثبت</button>
                                    <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                </div>
                            </div>
                        </div>
                    </td>
                    @endunless
                </tr>
                <tr>
                    <td>کد پستی</td>
                    <td>{{ $kyc->postal_code }}</td>
                    @unless ($kyc->status == 1)
                    <td class="form-box">
                        <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                        <div class="textarea">
                            <div class="card">
                                <div class="card-body">
                                    <textarea wire:model="postal_code_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary round btn-sm save"
                                        wire:click="save_errors('postal_code_err')">ثبت</button>
                                    <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                </div>
                            </div>
                        </div>
                    </td>
                    @endunless
                </tr>
                <tr>
                    <td>آدرس کامل</td>
                    <td>{{ $kyc->address }}</td>
                    @unless ($kyc->status == 1)
                    <td class="form-box">
                        <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                        <div class="textarea">
                            <div class="card">
                                <div class="card-body">
                                    <textarea wire:model="address_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-primary round btn-sm save"
                                        wire:click="save_errors('address_err')">ثبت</button>
                                    <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                </div>
                            </div>
                        </div>
                    </td>
                    @endunless
                </tr>
                <tr>
                    <td>کارت ملی</td>
                    <td>
                        <a href="{{ $kyc->melli_card }}" target="_blank">مشاهده</a>
                    </td>
                    @unless ($kyc->status == 1)
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
                    @endunless
                </tr>
                <tr>
                    <td>احراز مستند</td>
                    <td>
                        <a href="{{ $kyc->prove_picture }}" target="_blank">مشاهده </a>

                    </td>
                    @unless ($kyc->status == 1)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="prove_picture_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('prove_picture_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endunless
                </tr>
                <tr>
                    <td>رزومه</td>
                    <td>
                        @if ($kyc->resume)
                            <a href="{{ $kyc->resume }}" target="_blank">مشاهده</a>
                        @endif
                    </td>
                    @unless ($kyc->status == 1)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="resume_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('resume_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endunless
                </tr>
            </tbody>
        </table>

        @production
            <x-form.verification />
        @endproduction

        <x-slot name='footer'>
            @if ($kyc->status == 0)
                <button class="w-50 btn btn-primary round"
                    wire:click="save">ثبت</button>
            @endif
            <button class="btn btn-danger round w-25 mx-auto" data-bs-dismiss="modal">بستن</button>
        </x-slot>
    </x-modals.modal>
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
    </script>
@endscript
