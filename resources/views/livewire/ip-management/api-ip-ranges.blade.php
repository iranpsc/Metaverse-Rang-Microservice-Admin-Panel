<div>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#api-ip-range-modal">تعریف رنج IP</x-button>

    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#import-api-ip-range-modal">درون ریزی
        رنج آی پی
    </x-button>

    <x-button color="danger" wire:click="flushIpRanges">Flush</x-button>

    <x-form.search-box wire:model.live="searchTerm" />

    <x-modal id="api-ip-range-modal" title="تعریف رنج آی پی Api">
        <x-form.input name="title" label="عنوان" />

        <div class="form-group row">
            <label for="starting_ip" class="form-col-form-label col-sm-4">آی پی شروع</label>
            <div class="col-sm-4">
                <div class="row">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col">
                            <input class="form-control rounded" wire:model="starting_ip.{{ 3 - $i }}">
                            @error('starting_ip.' . (3 - $i))
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="ending_ip" class="form-col-form-label col-sm-4">آی پی پایان</label>
            <div class="col-sm-4">
                <div class="row">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col">
                            <input class="form-control rounded" wire:model="ending_ip.{{ 3 - $i }}">
                            @error('ending_ip.' . (3 - $i))
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <x-form.verification />

        <x-slot name="footer">
            <x-button wire:click="update">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modal>

    <x-modal id="import-api-ip-range-modal" title="درون ریزی رنج آی پی">
        <x-form.input name="title" label="عنوان" />
        <x-form.input type="file" name="file" label="فایل" />
        <x-form.verification />
        <x-slot name="footer">
            <x-button wire:click="import">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modal>

    @if (count($ip_ranges) > 0)
        <x-table id="ips-table">
            <x-slot name="headers">
                <th>عنوان</th>
                <th>از آی پی</th>
                <th>تا آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($ip_ranges as $ip_range)
                <tr wire:key="{{ $ip_range->id }}">
                    <td>{{ $ip_range->id }}</td>
                    <td>{{ $ip_range->title }}</td>
                    <td>{{ $ip_range->from }}</td>
                    <td>{{ $ip_range->to }}</td>
                    <td>{{ jdate($ip_range->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($ip_range->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-button color="danger" wire:click="delete({{ $ip_range->id }})" wire:confirm="آیا از حذف این مورد اطمینان دارید؟">حذف</x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $ip_ranges->links() }}
    @else
    <x-alert type="info" message="هیچ رنج آی پی ای برای نمایش وجود ندارد." />
    @endif
</div>
