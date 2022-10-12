    <div>
        <x-forms.search-box wire:model="search" placeholder="شناسه ملک را وارد کنید"></x-forms.search-box>

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
                            <button class="btn btn-primary btn-sm round" data-bs-toggle="modal"
                                data-bs-target="#modal-{{ explode('+', $feature->properties->id)[1] }}">ویرایش</button>


                            <button class="btn btn-success btn-sm round" data-bs-toggle="modal"
                                data-bs-target="#modal-{{ explode('+', $feature->properties->id)[1] . $loop->iteration }}">ویرایش
                                مختصات</button>
                        </td>
                    </tr>
                    @livewire('lands.edit.edit-feature-coordinate-modal', ['feature' => $feature, 'num' => $loop->iteration], key($feature->properties->id . $loop->iteration))
                    @livewire('lands.edit.edit-feature-modal', ['feature' => $feature], key($feature->properties->id))
                @endforeach
            </x-tables.table>
            {{ $features->links() }}
        @else
            <x-alerts.danger>ملکی یافت نشد</x-alerts.danger>
        @endif
    </div>
