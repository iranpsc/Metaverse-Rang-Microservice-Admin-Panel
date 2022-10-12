<div>


    <x-buttons.btn-success class="my-2" data-bs-toggle="modal" data-bs-target="#create-asset-modal">ایجاد ارز
    </x-buttons.btn-success>


    <x-modals.modal id="create-asset-modal" title="تعریف ارز">
        @if (session()->has('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        @if (session()->has('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif

        <x-forms.group for="asset" label="ارز">
            <x-forms.input type="text" id="package-color" wire:model="asset"
                placeholder="نام ارز را به انگلیسی وارد کنید" class="only-number" />
            @error('asset')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="price" label="قیمت واحد">
            <x-forms.input type="text" id="package-color" wire:model="price" class="only-number" />
            @error('price')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <div class="row form-group">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendSMS">ارسال پیامک تایید</x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <input type="text" class="form-control rounded" wire:model="phoneVerification">
                @error('phoneVerification')
                    <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <x-forms.group for="access_password" label="رمز دسترسی">
            <x-forms.input type="password" id="access_password" wire:model="access_password" class="only-number" />
            @error('access_password')
                <span class="form-text text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot:footer>
            <x-buttons.btn-primary class="btn-block" wire:click="save">ثبت</x-buttons.btn-primary>
            <x-buttons.btn-danger class="btn-block" data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
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
                        <x-buttons.btn-danger wire:click="delete({{ $variable->id }})">حذف</x-buttons.danger>
                            <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#edit-currency-modal-{{ $variable->id }}">بروزرسانی</x-buttons.primary>
                    </td>
                </tr>
                @livewire('variables.edit.edit-colors', ['asset' => $variable], key('assets-price-'.$variable->id))
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>ارزی تعریف نشده است</x-alerts.danger>
    @endif

</div>
