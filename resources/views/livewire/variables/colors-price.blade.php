<div>
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#create-asset-modal">ایجاد ارز
    </x-buttons.btn-primary>

    <x-modals.modal id="create-asset-modal" title="تعریف ارز">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="variables-asset" label="ارز">
            <x-forms.input id="variables-asset" wire:model="asset" placeholder="نام ارز را به انگلیسی وارد کنید" />
            @error('asset')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="price" label="قیمت واحد">
            <x-forms.input id="price" wire:model="price" class="only-number" />
            @error('price')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">ارسال پیامک تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="phoneVerification" />
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <x-forms.group for="variables-access-password" label="رمز دسترسی">
            <x-forms.input type="password" id="variables-access-password" wire:model="access_password" />
            @error('access_password')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot:footer>
            <x-buttons.btn-primary wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot:footer>
    </x-modals.modal>

    @if ($variables->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>نام ارز</th>
                <th>قیمت واحد</th>
                <th>آخرین بروز رسانی</th>
                <th>دلیل بروز رسانی</th>
                <th>مدیریت</th>
            </x-slot:headers>
            @foreach ($variables as $variable)
                <tr>

                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \App\Helpers\getAssetColor($variable->asset) }}</td>
                    <td>{{ $variable->price }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($variable->updated_at) }}</td>
                    <td>{{ $variable->note }}</td>
                    <td>
                        <x-buttons.btn-primary data-bs-toggle="modal"
                            data-bs-target="#edit-currency-modal-{{ $variable->id }}">بروزرسانی</x-buttons.primary>
                            <x-buttons.btn-danger class="confirm" title="deleteCurrency" id="{{ $variable->id }}">حذف
                                </x-buttons.danger>
                                @if ($variable->priceChangeLogs->count() > 0)
                                    <x-buttons.btn-info data-bs-toggle="modal"
                                        data-bs-target="#variable-history-{{ $variable->id }}">تاریخچه تغییرات
                                    </x-buttons.btn-info>
                                    <x-modals.modal size="modal-xl" id="variable-history-{{ $variable->id }}"
                                        title="تاریخچه تغییرات">
                                        <x-tables.table>
                                            <x-slot name="headers">
                    <th>دارایی</th>
                    <th>تاریخ تغییر</th>
                    <th>ساعت تغییر</th>
                    <th>تغییر دهنده</th>
                    <th>وضعیت گذشته</th>
                    <th>وضعیت حال</th>
                    <th>توضیحات</th>
                    <tbody>
                        @foreach ($variable->priceChangeLogs as $changeLog)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>ارز {{ \App\Helpers\getAssetColor($changeLog->changeable->asset) }}</td>
                                <td>{{ \Morilog\Jalali\Jalalian::forge($changeLog->created_at)->format('Y/m/d') }}
                                </td>
                                <td>{{ \Morilog\Jalali\Jalalian::forge($changeLog->created_at)->format('H:m:s') }}
                                </td>
                                <td>{{ $changeLog->changer_name }}</td>
                                <td>{{ $changeLog->previous_value }}</td>
                                <td>{{ $changeLog->current_value }}</td>
                                <td>{{ $changeLog->note }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    </x-slot>
                </x-tables.table>
                <x-slot:footer>
                    <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                </x-slot:footer>
        </x-modals.modal>
    @endif
    <livewire:variables.edit.edit-colors :asset="$variable" :wire:key="'edit-asset-price-'.$variable->id">
        </td>
        </tr>
        @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>ارزی تعریف نشده است</x-alerts.danger>
        @endif

</div>
