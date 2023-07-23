    <div>
        <x-forms.search-box wire:model.debounce.1000ms="search" placeholder="شناسه ملک را وارد کنید" />

        @if ($properties->count() > 0)
            <x-tables.table>
                <x-slot:headers>
                    <th>کد زمین</th>
                    <th>مساحت</th>
                    <th>تراکم</th>
                    <th>نوع کاربری</th>
                    <th>آدرس</th>
                    <th>تاریخ ثبت</th>
                    <th>ثبت کننده</th>
                    <th>ملاحضات</th>
                </x-slot:headers>
                @foreach ($properties as $property)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $property->id }}</td>
                        <td>{{ $property->area }}</td>
                        <td>{{ $property->density }}</td>
                        <td>{{ $property->getApplicationTitle() }}</td>
                        <td>{{ Str::limit($property->address, 15) }}</td>
                        <td>{{ jdate($property->date)->format('Y/m/d') }}</td>
                        <td>{{ $property->feature->map->publisher_name }}</td>
                        <td>
                            <x-buttons.btn-primary data-bs-toggle="modal"
                                data-bs-target="#modal-{{ explode('-', $property->id)[1] }}">ویرایش
                            </x-buttons.btn-primary>

                            <x-buttons.btn-success data-bs-toggle="modal"
                                data-bs-target="#modal-{{ $property->feature->id }}">
                                ویرایش
                                مختصات</x-buttons.btn-success>

                            <livewire:lands.edit.feature-properties :feature="$property->feature" :wire:key="'edit-properties-'.$property->feature->id">

                            <livewire:lands.edit.feature-coordinates :feature="$property->feature" :wire:key="'edit-coordinates-'.$property->feature->id">
                        </td>
                    </tr>
                @endforeach
            </x-tables.table>
            {{ $properties->links() }}
        @else
            <x-alerts.danger>ملکی یافت نشد</x-alerts.danger>
        @endif
    </div>
