<div>
    <x-modal id="map-modal-{{ $map->id }}" title="وارد کردن اطلاعات نقشه به دیتابیس">
        <x-table>
            <x-slot name="headers">
                <th>نام</th>
                <th>تعداد پالیگان</th>
                <th>کاربری</th>
            </x-slot>
            <tr>
                <td>#</td>
                <td>{{ $map->name }}</td>
                <td>{{ $map->polygon_count }}</td>
                <td>{{ $map->karbari }}</td>
            </tr>
        </x-table>

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="insertIntoDatabase({{ $map->id }})">ثبت
                نهایی</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>
</div>
