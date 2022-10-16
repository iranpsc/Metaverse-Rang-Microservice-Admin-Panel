<div>
    <x-buttons.btn-success class="my-2" data-bs-toggle="modal" data-bs-target="#create-package-modal">ایجاد پکیج رنگ
    </x-buttons.btn-success>

    <x-modals.modal id="create-package-modal" title="تعریف بسته">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif

        <x-forms.group for="asset" label="رنگ">
            <x-forms.select wire:model="asset">
                <option selected>ارز را انتخاب کنید</option>
                @forelse ($variables as $variable)
                    <option value="{{ $variable->asset }}">{{ \App\Helpers\getAssetColor($variable->asset) }}</option>
                @empty
                    <option disabled>ارزی تعریف نشده است</option>
                @endforelse
            </x-forms.select>
            @error('asset')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group label="تعداد" for="package-color">
            <x-forms.input type="text" id="package-color" wire:model="amount" placeholder="تعداد را وارد کنید"
                class="only-number" />
            @error('amount')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <div class="row form-group">
            <div class="col-sm-4">
                <a href="javascript:void(0)" class="btn btn-success btn-block btn-sm rounded" wire:click="sendSMS">
                    ارسال پیامک تایید
                </a>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control rounded only-number" wire:model="phoneVerification"
                    placeholder="تایید پیامکی">
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <x-forms.group label="رمز دسترسی" for="access_password">
            <x-forms.input type="password" id="access_password" wire:model="access_password" placeholder="رمز دسترسی"
                class="only-number" />
            @error('access_password')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-slot:footer>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            <x-buttons.btn-primary wire:click="save">ثبت</x-buttons.btn-primary>
        </x-slot:footer>
    </x-modals.modal>

    @if ($options->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>کد بسته</th>
                <th>ارز</th>
                <th>قیمت بسته</th>
                <th>تعداد</th>
                <th>تاریخ و ساعت بروزرسانی</th>
                <th>علت تغییر</th>
                <th>ملاحضات</th>
            </x-slot:headers>
            @forelse ($options as $option)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $option->code }}</td>
                    <td>{{ \App\Helpers\getAssetColor($option->asset) }}</td>
                    <td>{{ \App\Models\Variable::getRate($option->asset) * $option->amount }}</td>
                    <td>{{ $option->amount }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($option->update_at) }}</td>
                    <td>{{ $option->note }}</td>
                    <td>
                        <x-buttons.btn-danger wire:click="delete({{ $option->id }})">حذف</x-buttons.btn-danger>
                        <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#edit-package-modal-{{$option->id}}">بروز رسانی</x-buttons.btn-primary>
                    </td>
                </tr>
                @livewire('variables.edit.edit-options', ['option' => $option], key('options-'.$option->id))
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>پکیجی تعریف نشده است</x-alert>
    @endif
</div>
