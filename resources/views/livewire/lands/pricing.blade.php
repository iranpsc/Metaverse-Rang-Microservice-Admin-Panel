<div>
    <x-forms.search-box wire:model="search"></x-forms.search-box>

    @if ($pricings->count() > 0)
        <x-tables.table>
            <x-slot:headers>
                <th>کد زمین</th>
                <th>مبلغ قیمت گذاری psc</th>
                <th>مبلغ قیمت گذاری ریال</th>
                <th>تاریخ قیمت گذاری</th>
                <th>ساعت قیمت گذاری</th>
            </x-slot:headers>
            @foreach ($pricings as $pricing)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pricing->feature->properties->id }}</td>
                    <td>{{ $pricing->price_psc }}</td>
                    <td>{{ $pricing->price_irr }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($pricing->created_at)->format('Y/m/d') }}</td>
                    <td>{{ \Morilog\Jalali\Jalalian::forge($pricing->created_at)->format('H:m:s') }}</td>
                </tr>
            @endforeach
        </x-tables.table>
    @else
        <x-alerts.danger>ملکی قیمت گذاری نشده است</x-alerts.danger>
    @endif
</div>


