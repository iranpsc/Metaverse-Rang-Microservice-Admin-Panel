<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#admin-ip-modal">اضافه کردن IP
    </x-buttons.btn-primary>
    <x-modals.modal id="admin-ip-modal" title="تعریف آی پی دسترسی پنل ادمین">
        <x-forms.group  for="title" label="عنوان">
            <x-forms.input wire:model="title"/>
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

        <x-forms.verification />

        <x-slot name="footer">
            <x-buttons.btn-info wire:loading.attr="disabled" wire:click="save">ثبت</x-buttons.btn-info>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
    @if (count($ips) > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>عنوان</th>
                <th>آی پی</th>
                <th>تاریخ ایجاد</th>
                <th>ساعت ایجاد</th>
                <th>ملاحضات</th>
            </x-slot>
            @foreach ($ips as $ip)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ip->title }}</td>
                    <td>{{ $ip->from }}</td>
                    <td>{{ jdate($ip->created_at)->format('Y/m/d') }}</td>
                    <td>{{ jdate($ip->created_at)->format('H:m:s') }}</td>
                    <td>
                        <x-buttons.btn-danger id="delete-btn-{{ $ip->id }}">
                            <span class="fa fa-trash"></span>
                        </x-buttons.btn-danger>
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $ips->links() }}
    @else
        <x-alerts.danger>آی پی تعریف نشده است</x-alerts.danger>
    @endif

        <script>
            window.addEventListener('livewire:load', function() {
                @foreach ($ips as $ip)
                    $("#delete-btn-{{ $ip->id }}").click(function() {
                        Swal.fire({
                            title: 'آیا از حذف این آی پی مطمئن هستید؟',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText:'بله حذف کن',
                            cancelButtonText:'خیر'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                @this.call('delete', {{ $ip->id }})
                            }
                        })
                    })
                @endforeach
            })
        </script>
</div>
