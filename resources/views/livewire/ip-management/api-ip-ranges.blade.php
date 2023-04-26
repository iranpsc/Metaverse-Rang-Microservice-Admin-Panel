<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#api-ip-range-modal">تعریف رنج IP
    </x-buttons.btn-primary>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#import-api-ip-range-modal">درون ریزی
        رنج آی پی
    </x-buttons.btn-primary>
    <x-buttons.btn-danger wire:click="flushIpRanges">Flush</x-buttons.btn-danger>

    <x-forms.search-box wire:model="searchTerm" />

    <x-modals.modal id="api-ip-range-modal" title="تعریف رنج آی پی Api">
        <x-forms.group for="title" label="عنوان">
            <x-forms.input wire:model="title" />
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
           <x-forms.verification/>
            <x-slot name="footer">
                <x-buttons.btn-info wire:loading.attr="disabled" wire:click="update">ثبت</x-buttons.btn-info>
                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            </x-slot>
    </x-modals.modal>

    <x-modals.modal id="import-api-ip-range-modal" title="درون ریزی رنج آی پی">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="import-title" label="عنوان">
            <x-forms.input wire:model="title" id="import-file" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            </x-fomrs.gourp>
            <x-forms.group for="import-file" label="فایل">
                <x-forms.input type="file" wire:model="file" id="import-file" />
                <span class="text-success" wire:loading wire:target="file">در حال بارگذاری ...</span>
                @error('file')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                </x-fomrs.gourp>
               <x-forms.verification/>
                <x-slot name="footer">
                    <x-buttons.btn-info wire:loading.attr="disabled" wire:click="import">ثبت</x-buttons.btn-info>
                    <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
                </x-slot>
    </x-modals.modal>
    @if (count($ip_ranges) > 0)
        <x-tables.table id="ips-table">
            <x-slot name="headers">
                <th>عنوان</th>
                <th>از آی پی</th>
                <th>تا آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($ip_ranges as $ip_range)
                <tr>
                    <td>{{ $ip_range->id }}</td>
                    <td>{{ $ip_range->title }}</td>
                    <td>{{ $ip_range->from }}</td>
                    <td>{{ $ip_range->to }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($ip_range->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($ip_range->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-danger class="confirm" id="{{ $ip_range->id }}" title="deleteIpRange">حذف
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $ip_ranges->links() }}
    @else
        <x-alerts.danger>رنچ آی پی تعریف نشده است</x-alerts.danger>
    @endif
</div>
