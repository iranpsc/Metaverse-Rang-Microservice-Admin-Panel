<div>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#api-ip-modal">اضافه کردن IP</x-button>

    <x-modal id="api-ip-modal" title="تعریف آی پی دسترسی Api">
        <x-form.input name="title" label="عنوان" />

        <div class="form-group row">
            <label for="allowedIp" class="form-col-form-label col-sm-4">آی پی</label>
            <div class="col-sm-8">
                <div class="row">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col">
                            <input class="form-control rounded" wire:model="allowedIp.{{ 3 - $i }}" />
                            @error('allowedIp.' . (3 - $i))
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <x-form.verification />

        <x-slot name="footer">
            <x-button wire:click="save">ثبت</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if (count($allowedIps) > 0)
        <x-table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($allowedIps as $ip)
                <tr wire:key="{{ $ip->id }}">
                    <td>{{ $ip->id }}</td>
                    <td>{{ $ip->title }}</td>
                    <td>{{ $ip->from }}</td>
                    <td>{{ jdate($ip->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($ip->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-button color="danger" wire:click="delete({{ $ip->id }})"
                            wire:confirm="آیا از حذف این مورد اطمینان دارید؟">حذف</x-button>
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $allowedIps->links() }}
    @else
        <x-alert type="info" message="هیچ آی پی ای برای نمایش وجود ندارد." />
    @endif
</div>
