<div>

    <x-form.search-box wire:model.live="searchTerm" />

    @if ($bankAccounts->count() > 0)
        <x-table>
            <x-slot:headers>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>نام بانک</th>
                <th>شماره شبا</th>
                <th>شماره کارت</th>
                <th>وضعیت</th>
                <th>ملاحضات</th>
            </x-slot:headers>
            @foreach ($bankAccounts as $bankAccount)
                <tr wire:key="{{ $bankAccount->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $bankAccount->bankable->kyc->fname ?? $bankAccount->bankable->name }}</td>
                    <td>{{ $bankAccount->bankable->kyc->lname ?? '' }}</td>
                    <td>{{ $bankAccount->bank_name }}</td>
                    <td>{{ $bankAccount->shaba_num }}</td>
                    <td>{{ $bankAccount->card_num }}</td>
                    <td>
                        @if ($bankAccount->status == 0)
                            <x-badge type="warning">در انتظار بررسی</x-badge>
                        @elseif($bankAccount->status == 1)
                            <x-badge type="success">تایید شده</x-badge>
                        @else
                            <x-badge type="danger">رد شده</x-badge>
                        @endif
                    </td>
                    <td>
                        <x-button class="my-2" data-bs-toggle="modal"
                            data-bs-target="#view-bank-accounts-modal-{{ $bankAccount->id }}">
                            مشاهده
                        </x-button>
                        <x-modal id="view-bank-accounts-modal-{{ $bankAccount->id }}" title="جزئیات حساب بانکی">
                            <x-table>
                                <x-slot name="headers">
                    <th>عنوان</th>
                    <th>مقدار</th>
                    @unless ($bankAccount->status === 1)
                        <th>بررسی</th>
                    @endunless
                    </x-slot>
                <tr>
                    <td>1</td>
                    <td>نام بانک</td>
                    <td>{{ $bankAccount->bank_name }}</td>
                    @unless ($bankAccount->status === 1)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="bank_name_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('bank_name_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endunless
                </tr>
                <tr>
                    <td>2</td>
                    <td>شماره شبا</td>
                    <td>{{ $bankAccount->shaba_num }}</td>
                    @unless ($bankAccount->status === 1)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="shaba_num_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('shaba_num_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endunless
                </tr>
                <tr>
                    <td>3</td>
                    <td>شماره کارت</td>
                    <td>{{ $bankAccount->card_num }}</td>
                    @unless ($bankAccount->status === 1)
                        <td class="form-box">
                            <button class="btn btn-danger btn-sm round reject">وارد کردن دلیل اشکال</button>
                            <div class="textarea">
                                <div class="card">
                                    <div class="card-body">
                                        <textarea wire:model="card_num_err" class="form-control rounded" cols="20" rows="2"></textarea>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-primary round btn-sm save"
                                            wire:click="save_errors('card_num_err')">ثبت</button>
                                        <button class="btn btn-danger round btn-sm close-btn">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    @endunless
                </tr>
        </x-table>
        <x-slot name="footer">
            @if ($bankAccount->status == 0)
                <button class="mx-auto btn btn-primary round w-50" wire:click="save({{ $bankAccount }})">ثبت</button>
            @endif
            <button class="btn btn-danger round mx-auto w-25" data-bs-dismiss="modal">بستن</button>
        </x-slot>
        </x-modals.modal>
        </td>
        </tr>
    @endforeach
    </x-table>

    {{ $bankAccounts->links() }}
@else
    <x-alert type="warning" message="کاربری یافت نشد." />
    @endif
</div>

@assets
    <script src="{{ asset('assets/plugins/jquery/dist/jquery-3.1.0.js') }}"></script>

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
