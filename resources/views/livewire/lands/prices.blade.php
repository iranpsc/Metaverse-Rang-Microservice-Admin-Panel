<div>
    <x-form.search-box wire:model="search"/>

    @if (count($features) > 0)
        <x-table>
            <x-slot:headers>
                <th>کد زمین</th>
                <th>کاربری</th>
                <th>قیمت اولیه</th>
                <th>درصد پیشنهادی</th>
                <th>تاریخ ثبت پیشنهاد قیمت</th>
            </x-slot:headers>
            @foreach ($features as $feature)
                <tr wire:key="{{ $feature->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $feature->properties->id }}</td>
                    <td>{{ $feature->properties->getApplicationTitle() }}</td>
                    <td>{{ $feature->properties->stability }}</td>
                    <td>{{ $feature->properties->minimum_price_percentage }}</td>
                    <td>{{ jdate($feature->properties->updated_at)->format('Y/m/d')}}</td>
                </tr>
            @endforeach
        </x-table>
        {{ $features->links() }}
    @else
        <x-alert type="danger" :message="'ملکی یافت نشد'"/>
    @endif
</div>
