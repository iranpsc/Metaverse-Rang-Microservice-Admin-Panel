<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}

    @if (empty($ip_ranges))
        <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#api-ip-range-modal">تعریف رنج IP
        </x-buttons.btn-primary>
    @else
        <x-tables.table>
            <x-slot name="headers">
                <th>از آی پی</th>
                <th>تا آی پی</th>
                <th>ملاحضات</th>
            </x-slot>
            <tr>
                <td>1</td>
                <td>{{ $ip_ranges['from'] }}</td>
                <td>{{ $ip_ranges['to'] }}</td>
                <td>
                    <x-buttons.btn-info data-bs-toggle="modal" data-bs-target="#api-ip-range-modal">ویرایش
                    </x-buttons.btn-info>
                </td>
            </tr>
        </x-tables.table>
    @endif
    <x-modals.modal id="api-ip-range-modal" title="تعریف رنج آی پی Api">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if (session('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
        <x-forms.group for="from" label="آی پی شروع">
            <div class="row">
                @for ($i = 0; $i < 4; $i++)
                    <div class="col">
                        <x-forms.input wire:model="from.{{ 3 - $i }}" />
                        @error('from.' . (3 - $i))
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endfor
            </div>
        </x-forms.group>
        <x-forms.group for="to" label="آی پی پایانی">
            <div class="row">
                @for ($i = 0; $i < 4; $i++)
                    <div class="col">
                        <x-forms.input wire:model="to.{{ 3 - $i }}" />
                        @error('to.' . (3 - $i))
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endfor
            </div>
        </x-forms.group>
        <div class="form-group row">
            <div class="col-sm-4">
                <x-buttons.btn-success wire:loading.attr="disabled" wire:click="sendCode">ارسال کد تایید
                </x-buttons.btn-success>
            </div>
            <div class="col-sm-8">
                <x-forms.input wire:model="code" />
                @error('code')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <x-forms.group for="access-password" label="رمز دسترسی">
            <x-forms.input type="password" id="access-password" wire:model="accessPassword" />
            @error('accessPassword')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-info wire:loading.attr="disabled" wire:click="update">ثبت</x-buttons.btn-info>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
</div>
