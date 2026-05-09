<div>
    <x-button class="my-2" data-bs-toggle="modal" data-bs-target="#upload-map-modal">بارگذاری نقشه</x-button>

    <x-modal id="upload-map-modal" title="بارگذاری فایل نقشه">
        <x-form.input name="name" label="نام آبادی" />

        <x-form.input type="file" name="map_file" label="فایل نقشه" />

        <x-form.input type="file" name="point_file" label="فایل نقطه مرکزی" />

        <x-form.input type="file" name="border_file" label="فایل مرز" />

        <x-form.input type="color" name="color" label="رنگ محدوده" />

        @production
            <x-form.verification />
        @endproduction

        <x-slot name="footer">
            <x-button wire:loading.attr="disabled" wire:click="save">بارگذاری</x-button>
            <x-button color="danger" data-bs-dismiss="modal">بستن</x-button>
        </x-slot>
    </x-modals.modal>

    @if ($maps->count() > 0)
        <x-table>
            <x-slot name="headers">
                <th>نام آبادی</th>
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
                <tr wire:key="{{ $map->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $map->name }}</td>
                    <td>{{ $map->publish_date }}</td>
                    <td>{{ $map->publisher_name }}</td>
                    <td>{{ $map->polygon_count }}</td>
                    <td>{{ $map->total_area }}</td>
                    <td>{{ $map->first_id }}</td>
                    <td>{{ $map->last_id }}</td>
                    <td>
                        @if ($map->status)
                            <span class="badge bg-success">منتشر شده</span>
                        @else
                            <span class="badge bg-danger">منتشر نشده</span>
                        @endif
                    </td>
                    <td>
                        @unless ($map->isPublished())
                            <x-button data-bs-toggle="modal"
                                data-bs-target="#map-modal-{{ $map->id }}">انتشار</x-button>
                        @endunless

                        <x-button data-bs-toggle="modal"
                            data-bs-target="#update-map-modal-{{ $map->id }}">ویرایش</x-button>

                        <x-button color="danger" wire:confirm="می خواهید حذف کنید؟"
                            wire:click="delete({{ $map->id }})">حذف</x-button>

                        <livewire:maps.update :map="$map" :wire:key="'map-update-' . $map->id" />
                        <livewire:maps.insert-into-database :$map :key="$map->id" />
                    </td>
                </tr>
            @endforeach
        </x-table>
        {{ $maps->links() }}
    @else
        <x-alert type="warning" message="هیچ نقشه ای برای نمایش وجود ندارد." />
    @endif
</div>
