<div>
    <x-forms.search-box wire:model="search"></x-forms.search-box>
    @if (count($features) > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>کد زمین</th>
                <th>کاربری</th>
                <th>قیمت اولیه</th>
                <th>درصد پیشنهادی</th>
                <th>تاریخ ثبت پیشنهاد قیمت</th>
            </x-slot:headers>
            @foreach ($features as $feature)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $feature->properties->id }}</td>
                    <td>{{ $feature->properties->getApplicationTitle() }}</td>
                    <td>{{ $feature->properties->stability }}</td>
                    <td>{{ $feature->properties->minimum_price_percentage }}</td>
                    <td>{{ jdate($feature->properties->updated_at)->format('Y/m/d')}}</td>
                </tr>
            @endforeach
        </x-tables.table>
        {{ $features->links() }}
    @else
        <x-alerts.danger>ملکی یافت نشد</x-alerts.danger>
    @endif
</div>
