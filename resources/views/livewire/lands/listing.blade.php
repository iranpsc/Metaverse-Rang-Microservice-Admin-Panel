    <div>
        <x-form.search-box wire:model.live="search" placeholder="شناسه ملک را وارد کنید" />

        @if ($properties->count() > 0)
            <x-table>
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
                    <tr wire:key="{{ $property->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $property->id }}</td>
                        <td>{{ $property->area }}</td>
                        <td>{{ $property->density }}</td>
                        <td>{{ $property->getApplicationTitle() }}</td>
                        <td>{{ Str::limit($property->address, 15) }}</td>
                        <td>{{ jdate($property->date)->format('Y/m/d') }}</td>
                        <td>{{ $property->feature->map->publisher_name }}</td>
                        <td>
                            <x-button color="primary" data-bs-toggle="modal"
                                data-bs-target="#modal-{{ explode('-', $property->id)[1] }}">
                                ویرایش
                            </x-button>

                            <x-button color="success" data-bs-toggle="modal"
                                data-bs-target="#modal-{{ $property->feature->id }}">
                                ویرایش مختصات
                            </x-button>

                            <livewire:lands.edit.feature-properties :feature="$property->feature" :key="'edit-properties-' . $property->feature->id" />

                            <livewire:lands.edit.feature-coordinates :feature="$property->feature" :key="'edit-coordinates-' . $property->feature->id" />
                        </td>
                    </tr>
                @endforeach
            </x-table>
            {{ $properties->links() }}
        @else
            <x-alert type="danger" :message="'ملکی یافت نشد'" />
        @endif
    </div>
