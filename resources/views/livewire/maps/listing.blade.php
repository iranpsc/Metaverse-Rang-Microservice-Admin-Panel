<div>
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#upload-map-modal">بارگذاری نقشه</x-buttons.btn-primary>
    <x-modals.modal id="upload-map-modal" title="بارگذاری فایل نقشه">
        <x-forms.group for="name" label="نام آبادی">
            <x-forms.input wire:model="name" id="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.group for="file" label="بارگذاری نقشه">
            <x-forms.input type="file" wire:model="file" id="file" />
            <x-progress-bar wire:loading wire:target="file" />
            @error('file')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>

        <x-forms.verification/>

        <x-slot name="footer">
            <x-buttons.btn-success wire:loading.attr="disabled" wire:click="save">بارگذاری</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>
    @if ($maps->count() > 0)
        <x-tables.table>
            <x-slot name="headers">
                <th>نام آبادی</th>
                <th>کاربری</th>
                <th>تاریخ انتشار</th>
                <th>نام منتشر کننده</th>
                <th>تعداد پالیگان</th>
                <th>مساحت کل</th>
                <th>آیدی اولین زمین</th>
                <th>آیدی آخرین زمین</th>
                <th>وضعیت</th>
                <th>مدیریت</th>
            </x-slot>
            @foreach ($maps as $map)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $map->name }}</td>
                    <td>{{ $map->karbari }}</td>
                    <td>{{ $map->publish_date }}</td>
                    <td>{{ $map->publisher_name }}</td>
                    <td>{{ $map->polygon_count }}</td>
                    <td>{{ $map->total_area }}</td>
                    <td>{{ $map->first_id }}</td>
                    <td>{{ $map->last_id }}</td>
                    <td>{{ $map->status }}</td>
                    <td>
                        @unless($map->status == 1)
                            <x-buttons.btn-primary data-bs-toggle="modal" data-bs-target="#map-modal-{{ $map->id }}">اعمال</x-buttons.btn-primary>
                            <x-buttons.btn-danger class="confirm" id="{{ $map->id }}" title="deleteMap">حذف</x-buttons.btn-danger>
                        @endunless
                        <livewire:maps.insert-into-database' :map="$map" wire:key="'map-' . $map->id"/>                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $maps->links() }}
    @else
        <x-alerts.danger>نقشه ای یافت نشد</x-alerts.danger>
    @endif
</div>
