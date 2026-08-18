<div>
    <x-button data-bs-toggle="modal" data-bs-target="#modal" class="mb-2">
        <span class="fa fa-plus"></span>
        ایجاد محدودیت
    </x-button>

    <x-modal size="modal-xl" id="modal" title="تعریف محدودیت">
        <div class="alert alert-danger">
            <p>
                <strong>توجه:</strong>
                تاریخ شروع و پایان نباید با دیگر محدودیت ها تداخل داشته باشد.
            </p>
            <p>
                <strong>توجه:</strong>
                پیشوند شناسه های شروع و پایان باید با یکدیگر یکسان باشند.
            </p>
        </div>
        <x-form.input name="title" label="عنوان" />

        <div class="row">
            <div class="col-md-6">
                <x-form.input name="start_id" label="شناسه شروع" />
            </div>
            <div class="col-md-6">
                <x-form.input name="end_id" label="شناسه پایانی" />
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="verified_kyc_limit"
                            name="verified_kyc_limit" wire:model="verified_kyc_limit">
                        <label class="form-check-label" for="verified_kyc_limit">محدودیت احراز هویت تایید شده</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="verified_bank_account_limit"
                            name="verified_bank_account_limit" wire:model="verified_bank_account_limit">
                        <label class="form-check-label" for="verified_bank_account_limit">محدودیت حساب بانکی تایید
                            شده</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="not_sellable" name="not_sellable"
                            wire:model="not_sellable">
                        <label class="form-check-label" for="not_sellable">غیرقابل فروش</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="under_18_limit" name="under_18_limit"
                            wire:model="under_18_limit">
                        <label class="form-check-label" for="under_18_limit">محدودیت زیر ۱۸ سال</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="more_than_18_limit"
                            name="more_than_18_limit" wire:model="more_than_18_limit">
                        <label class="form-check-label" for="more_than_18_limit">محدودیت بالای ۱۸ سال</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="dynasty_owner_limit"
                            name="dynasty_owner_limit" wire:model="dynasty_owner_limit">
                        <label class="form-check-label" for="dynasty_owner_limit">محدودیت دارنده سلسله</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <div class="row border">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="individual_buy_limit"
                                name="individual_buy_limit" wire:model="individual_buy_limit">
                            <label class="form-check-label" for="individual_buy_limit">محدودیت تعداد خرید</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <x-form.input name="individual_buy_count" label="تعداد خرید" />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row border">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="price_limit" name="price_limit"
                                wire:model="price_limit">
                            <label class="form-check-label" for="price_limit">محدودیت قیمت ثابت</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <x-form.input name="price" label="قیمت ثابت" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-form.input type="date" name="start_date" label="تاریخ شروع" />
            </div>
            <div class="col-md-6">
                <x-form.input type="date" name="end_date" label="تاریخ پایان" />
            </div>
        </div>

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بازگشت</x-button>
        </x-slot>
    </x-modal>

    @if ($feature_limits->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>تاریخ شروع</th>
                <th>تاریخ پایان</th>
                <th>شناسه شروع</th>
                <th>شناسه پایانی</th>
                <th>محدودیت ها</th>
                <th>وضعیت</th>
                <th>اقدامات</th>
            </x-slot>

            @foreach ($feature_limits as $limit)
                <tr wire:key="{{ $limit->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $limit->title }}</td>
                    <td>{{ jdate($limit->start_date)->format('Y/m/d') }}</td>
                    <td>{{ jdate($limit->end_date)->format('Y/m/d') }}</td>
                    <td>{{ $limit->start_id }}</td>
                    <td>{{ $limit->end_id }}</td>
                    <td>
                        <ul>
                            <li>{{ $limit->verified_kyc_limit ? 'محدودیت احراز هویت تایید شده' : '' }}</li>
                            <li>{{ $limit->verified_bank_account_limit ? 'محدودیت حساب بانکی تایید شده' : '' }}</li>
                            <li>{{ $limit->not_sellable ? 'غیرقابل فروش' : '' }}</li>
                            <li>{{ $limit->under_18_limit ? 'محدودیت زیر ۱۸ سال' : '' }}</li>
                            <li>{{ $limit->more_than_18_limit ? 'محدودیت بالای ۱۸ سال' : '' }}</li>
                            <li>{{ $limit->dynasty_owner_limit ? 'محدودیت دارنده سلسله' : '' }}</li>
                        </ul>
                    </td>
                    <td>
                        @if($limit->expired)
                            <span class="badge bg-danger">منقضی شده</span>
                        @else
                            <span class="badge bg-success">فعال</span>
                        @endif
                    </td>
                    <td>
                        <x-button color="danger" wire:confirm="آیا می خواهید حذف کنید؟"
                            wire:click="delete({{ $limit->id }})">
                            <span class="fa fa-trash"></span>
                            حذف
                        </x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{ $feature_limits->links() }}
    @else
        <x-alert type="warning" message="محدودیتی یافت نشد!" />
    @endif
</div>
