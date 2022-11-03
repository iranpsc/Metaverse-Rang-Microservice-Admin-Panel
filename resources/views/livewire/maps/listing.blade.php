<div>
    {{-- Care about people's approval and you will be their prisoner. --}}
    <x-buttons.btn-primary class="my-2" data-bs-toggle="modal" data-bs-target="#upload-map-modal">بارگزاری نقشه
    </x-buttons.btn-primary>
    <a href="{{ route('empty-and-reset-database') }}" class="btn btn-danger btn-sm rounded">حذف اطلاعات نقشه ها از
        دیتابیس</a>
    <x-modals.modal id="upload-map-modal" title="بارگزاری فایل نقشه">
        @if (session('success'))
            <x-alerts.success>{{ session('success') }}</x-alerts.success>
        @endif
        <x-forms.group for="name" label="نام آبادی">
            <x-forms.input wire:model="name" id="name" />
            @error('name')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-forms.group for="file" label="بارگزاری نقشه">
            <x-forms.input type="file" wire:model="file" id="file" />
            <span class="text-success" wire:loading wire:target="file">در حال بارگذاری ...</span>
            @error('file')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </x-forms.group>
        <x-slot name="footer">
            <x-buttons.btn-success wire:click="save">آپلود</x-buttons.btn-success>
            <x-buttons.btn-danger data-bs-dismiss="modal">بستن</x-buttons.btn-danger>
        </x-slot>
    </x-modals.modal>

    @if ($polygons->count() > 0)
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
            @foreach ($polygons as $polygon)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $polygon->name }}</td>
                    <td>{{ $polygon->karbari }}</td>
                    <td>{{ $polygon->publish_date }}</td>
                    <td>{{ $polygon->publisher_name }}</td>
                    <td>{{ $polygon->polygon_count }}</td>
                    <td>{{ $polygon->total_area }}</td>
                    <td>{{ $polygon->first_id }}</td>
                    <td>{{ $polygon->last_id }}</td>
                    <td>
                        @switch($polygon->status)
                            @case(0)
                                <span class="badge badge-warning">منتشر نشده</span>
                            @break

                            @case(1)
                                <span class="badge badge-success">منتشر شده</span>
                            @break

                            @default
                        @endswitch
                    </td>
                    <td>
                        @unless($polygon->status == 1)
                            <x-buttons.btn-primary data-bs-toggle="modal"
                                data-bs-target="#polygon-modal-{{ $polygon->id }}">
                                اعمال</x-buttons.btn-primary>
                            <x-buttons.btn-danger class="confirm" id="{{ $polygon->id }}" title="deletePolygon">حذف
                            </x-buttons.btn-danger>
                        @endunless
                        @livewire('maps.insert-into-database', ['polygon' => $polygon], key('polygon-' . $polygon->id))
                    </td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $polygons->links() }}
    @else
        <x-alerts.danger>نقشه ای یافت نشد</x-alerts.danger>
    @endif
</div>
