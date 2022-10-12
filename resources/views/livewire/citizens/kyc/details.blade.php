<div>
    <div id="modal-kyc-{{ $kyc->id }}" wire:ignore.self class="modal fade" data-bs-backdrop="static" role="dialog"
        tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h4 class="modal-title">جزئیات اطلاعات کاربر</h4>
                </div>
                <div class="modal-body">
                    @if (session()->has('success'))
                        <x-alerts.success>{{ session('success') }}</x-alerts.success>
                    @endif
                    @if (session()->has('error'))
                        <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
                    @endif
                    <table class="table table-bordered table-hover table-striped text-center">
                        <tbody>
                            <tr>
                                <td>نام</td>
                                <td>{{ $kyc->fname }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="fname_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('fname_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>نام خانوادگی</td>
                                <td>{{ $kyc->lname }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="lname_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('lname_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>نام پدر</td>
                                <td>{{ $kyc->father_name }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="father_name_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('father_name_err' )">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>کد ملی</td>
                                <td>{{ $kyc->melli_code }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="melli_code_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('melli_code_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>استان </td>
                                <td>{{ $kyc->province }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="province_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('province_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>شهر</td>
                                <td>{{ $kyc->city }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="city_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('city_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>پلاک</td>
                                <td>{{ $kyc->number }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="number_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('number_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>کد پستی</td>
                                <td>{{ $kyc->postal_code }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="postal_code_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('postal_code_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>آدرس کامل</td>
                                <td>{{ $kyc->address }}</td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="address_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('address_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>کارت ملی</td>
                                <td>
                                    <a href="{{ url($kyc->melli_card) }}"
                                        target="_blank">مشاهده</a>
                                </td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="melli_card_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('melli_card_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>احراز مستند</td>
                                <td>
                                    <a href="{{ url($kyc->prove_picture) }}"
                                        target="_blank">مشاهده </a>

                                </td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="prove_picture_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('prove_picture_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>رزومه</td>
                                <td>
                                    <a href="{{ url($kyc->resume) }}"
                                        target="_blank">مشاهده</a>
                                </td>
                                <td class="form-box">
                                    <button class="btn btn-primary btn-sm round">تایید</button>
                                    <button class="btn btn-danger btn-sm round reject">رد</button>
                                    <div class="textarea">
                                        <div class="card">
                                            <div class="card-body">
                                                <textarea wire:model.defer="resume_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                            </div>
                                            <div class="card-footer">
                                                <button class="btn btn-primary round btn-sm save"
                                                    wire:click="save_errors('resume_err')">ثبت</button>
                                                <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <button @if ($kyc->status == 1) disabled @endif
                                        class="btn btn-primary round w-25" wire:click="save">ثبت</button>
                                    <button class="btn btn-danger round w-25" data-bs-dismiss="modal">بستن</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
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
@endpush

@push('css')
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
@endpush
