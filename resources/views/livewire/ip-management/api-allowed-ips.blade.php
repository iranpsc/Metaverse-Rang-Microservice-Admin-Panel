<div>
    {{-- The best athlete wants his opponent at his best. --}}
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#api-ip-modal">اضافه کردن IP
    </x-buttons.btn-primary>
    <x-modals.modal id="api-ip-modal" title="تعریف آی پی دسترسی Api">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif

        <x-forms.group for="title" label="عنوان">
            <x-forms.input wire:model="title" />
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-fomrs.gourp>
            <x-forms.group for="ip" label="آی پی">
                <div class="row">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="col">
                            <x-forms.input wire:model="allowedIp.{{ 3 - $i }}" />
                            @error('allowedIp.' . (3 - $i))
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    @endfor
                </div>
            </x-forms.group>
           <x-forms.verification/>
            <x-slot name="footer">
                <x-buttons.btn-info wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-info>
                <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
            </x-slot>
    </x-modals.modal>
    @if (count($allowedIps) > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($allowedIps as $allowedIp)
                <tr>
                    <td>{{ $allowedIp->id }}</td>
                    <td>{{ $allowedIp->title }}</td>
                    <td>{{ $allowedIp->from }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($allowedIp->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($allowedIp->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-danger class="confirm" id="{{ $allowedIp->id }}" title="deleteApiIp">حذف
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $allowedIps->links() }}
    @else
        <x-alerts.danger>آی پی تعریف نشده است</x-alerts.danger>
    @endif
</div>
