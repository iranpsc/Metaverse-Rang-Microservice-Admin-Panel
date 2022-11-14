<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#api-ip-range-modal">تعریف رنج IP
    </x-buttons.btn-primary>
    <x-modals.modal id="api-ip-range-modal" title="تعریف رنج آی پی Api">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        @if (session('error'))
            <x-alerts.danger>{{ session('error') }}</x-alerts.danger>
        @endif
        <x-forms.group  for="title" label="عنوان">
            <x-forms.input wire:model="title"/>
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-fomrs.gourp>
        <x-forms.group for="starting_ip" label="آی پی شروع">
            <div class="row">
                @for ($i = 0; $i < 4; $i++)
                    <div class="col">
                        <x-forms.input wire:model="starting_ip.{{ 3 - $i }}" />
                        @error('starting_ip.' . (3 - $i))
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                @endfor
            </div>
        </x-forms.group>
        <x-forms.group for="ending_ip" label="آی پی پایانی">
            <div class="row">
                @for ($i = 0; $i < 4; $i++)
                    <div class="col">
                        <x-forms.input wire:model="ending_ip.{{ 3 - $i }}" />
                        @error('ending_ip.' . (3 - $i))
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
    @if (count($ip_ranges) > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>از آی پی</th>
                <th>تا آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ایجاد کننده</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($ip_ranges as $key => $ip_range)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ip_range['title'] }}</td>
                    <td>{{ $ip_range['starting_ip'] }}</td>
                    <td>{{ $ip_range['ending_ip'] }}</td>
                    <td>{{ $ip_range['created_date'] }}</td>
                    <td>{{ $ip_range['created_hour'] }}</td>
                    <td>{{ $ip_range['created_by'] }}</td>
                    <td>
                        <x-buttons.btn-danger class="confirm" id="{{ $key }}" title="deleteIpRange">حذف
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>رنچ آی پی تعریف نشده است</x-alerts.danger>
    @endif
</div>
