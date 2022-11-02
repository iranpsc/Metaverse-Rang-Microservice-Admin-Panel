    <div>
        <x-forms.search-box wire:model.debounce.1000ms="search" placeholder="شناسه ملک را وارد کنید"></x-forms.search-box>

        @if ($features->count() > 0)
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
                @foreach ($features as $feature)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $feature->properties->id }}</td>
                        <td>{{ $feature->properties->area }}</td>
                        <td>{{ $feature->properties->density }}</td>
                        <td>{{ \App\Helpers\Feature::getKarbari($feature->properties->karbari) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($feature->properties->address, 15) }}</td>
                        <td>{{ \Morilog\Jalali\Jalalian::forge($feature->properties->date)->format('Y/m/d') }}</td>
                        <td>مدیر سایت</td>
                        <td>
                            <x-buttons.btn-primary data-bs-toggle="modal"
                                data-bs-target="#modal-{{ explode('+', $feature->properties->id)[1] }}">ویرایش
                            </x-buttons.btn-primary>

                            <x-buttons.btn-success data-bs-toggle="modal" data-bs-target="#modal-{{ $feature->id }}">
                                ویرایش
                                مختصات</x-buttons.btn-success>

                            <livewire:lands.edit.feature-properties :feature="$feature"
                                :wire:key="'edit-properties-'.$feature->properties->id">

                                <livewire:lands.edit.feature-coordinates :feature="$feature"
                                    :wire:key="'edit-coordinates-'.$feature->id">
                        </td>
                    </tr>
                @endforeach
            </x-tables.table>
            {{ $features->links() }}
        @else
            <x-alerts.danger>ملکی یافت نشد</x-alerts.danger>
        @endif
    </div>
